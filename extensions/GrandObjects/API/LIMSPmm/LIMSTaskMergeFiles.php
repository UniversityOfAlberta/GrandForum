<?php

class LIMSTaskMergeFiles extends RESTAPI {

    function doPOST() {
        $this->throwError("POST method not supported for this endpoint.", 405);
    }

    function doPUT() {
        $this->throwError("PUT method not supported for this endpoint.", 405);
    }

    function doDELETE() {
        $this->throwError("DELETE method not supported for this endpoint.", 405);
    }
 
    function doGET(){
        $task_id = $this->getParam('id');
        if (empty($task_id)) {
            $this->throwError("Task ID not found in URL", 400);
            return;
        }

        $format = isset($_GET['format']) ? $_GET['format'] : '';
        if (empty($format) || !in_array($format, ['csv', 'pdf'])) {
            $this->throwError("A valid 'format' parameter ('csv' or 'pdf') is required.", 400);
            return;
        }

        $task = LIMSTaskPmm::newFromId($task_id);
        $all_files_metadata = $task->files;

        switch ($format) {
            case 'csv':
                $this->mergeAndStreamCsv($task, $all_files_metadata);
                break;
            
            case 'pdf':
                $this->mergeAndStreamPdf($task, $all_files_metadata);
                break;
        }
    }
    private function mergeAndStreamCsv($task, $all_files_metadata) {
        $csv_files_data = [];

        foreach ((array)$all_files_metadata as $file_meta) {
            if (isset($file_meta['type']) && $file_meta['type'] == 'text/csv') {
                $full_file = $task->getFile($file_meta['id']);
                if (isset($full_file['data'])) {
                    $exploded = explode("base64,", $full_file['data']);
                    $decoded_content = base64_decode(end($exploded));
                    if ($decoded_content) {
                        $assigneePerson = Person::newFromId($file_meta['assignee']);
                        $assigneeName = $assigneePerson ? $assigneePerson->getName() : 'Unknown';
                        
                        $csv_files_data[] = [
                            'content' => $decoded_content,
                            'assignee_name' => $assigneeName,
                            'timestamp' => $file_meta['created_at'] ?? date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        }

        if (empty($csv_files_data)) {
            $this->throwError("No CSV files found for this task.", 404);
            return;
        }

        $merged_csv_data = [];
        $header_written = false;

        foreach ($csv_files_data as $file_data) {
            $content_rows = explode("\n", $file_data['content']);
            $is_first_row_of_file = true;

            foreach ($content_rows as $row_string) {
                if (empty(trim($row_string))) continue;
                $row_array = str_getcsv($row_string);
                
                if (!$header_written) {
                    $header_row = $row_array;
                    $header_row[] = 'Assigned';
                    $header_row[] = 'Timestamp';
                    $merged_csv_data[] = $header_row;
                    $header_written = true;
                    $is_first_row_of_file = false;
                } else if (!$is_first_row_of_file) { // Skip subsequent headers
                    $data_row = $row_array;
                    $data_row[] = $file_data['assignee_name'];
                    $data_row[] = $file_data['timestamp'];
                    $merged_csv_data[] = $data_row;
                }
                $is_first_row_of_file = false;
            }
        }
        
        $stream = fopen('php://memory', 'w');
        foreach ($merged_csv_data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $final_csv_string = stream_get_contents($stream);
        fclose($stream);
        
        $filename = "merged_task_" . $task->id . "_" . date("Y-m-d") . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        
        echo $final_csv_string;
        exit;
    }

    private function mergeAndStreamPdf($task, $all_files_metadata) {
        $temp_pdf_paths = [];

        foreach ((array)$all_files_metadata as $file_meta) {
            if (isset($file_meta['type']) && $file_meta['type'] == 'application/pdf') {
                $full_file = $task->getFile($file_meta['id']);
                if (isset($full_file['data'])) {
                    $exploded = explode("base64,", $full_file['data']);
                    $decoded_content = base64_decode(end($exploded));
                    if ($decoded_content) {
                        $temp_file = tempnam(sys_get_temp_dir(), 'task-pdf-');
                        file_put_contents($temp_file, $decoded_content);
                        $temp_pdf_paths[] = $temp_file;
                    }
                }
            }
        }

        if (empty($temp_pdf_paths)) {
            $this->throwError("No PDF files found for this task.", 404);
            return;
        }

        $merged_pdf_path = tempnam(sys_get_temp_dir(), 'merged-pdf-');
        $success = $this->executePdfMerge($temp_pdf_paths, $merged_pdf_path);

        foreach ($temp_pdf_paths as $path) {
            unlink($path);
        }

        if (!$success) {
            unlink($merged_pdf_path);
            $this->throwError("Failed to merge PDF files on the server.", 500);
            return;
        }

        $filename = "merged_task_" . $task->id . "_" . date("Y-m-d") . ".pdf";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($merged_pdf_path));

        readfile($merged_pdf_path);
        unlink($merged_pdf_path);
        exit;
    }

    private function executePdfMerge(array $filePaths, string $outputFilePath): bool {
        $escapedFilePaths = array_map('escapeshellarg', $filePaths);
        $escapedOutput = escapeshellarg($outputFilePath);
        $command = "pdftk " . implode(' ', $escapedFilePaths) . " cat output " . $escapedOutput;
        shell_exec($command);

        return file_exists($outputFilePath) && filesize($outputFilePath) > 0;
    }
}
?>
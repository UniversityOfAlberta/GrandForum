<?php

class LIMSTaskMergeCSV extends RESTAPI {

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
        // Get the task ID from the URL action
       $task_id = $this->getParam('id');


        if (empty($task_id)) {
            $this->throwError("Task ID not found in URL", 400);
            return;
        }

        $task = LIMSTaskPmm::newFromId($task_id);
        
        $all_files_metadata = $task->files; 
        $csv_files_content = [];

        $csv_mime_types = [
            'text/csv', 
            // 'application/csv', 
            // 'application/vnd.ms-excel', 
            // 'text/plain'
        ];

        // Step 1: Filter for CSV files using a more flexible check
        foreach ((array)$all_files_metadata as $file_meta) {
            $is_csv_by_type = isset($file_meta['type']) && in_array($file_meta['type'], $csv_mime_types);
            
            $is_csv_by_name = isset($file_meta['filename']) && strtolower(substr($file_meta['filename'], -4)) === '.csv';

            if ($is_csv_by_type || $is_csv_by_name) {
                $full_file = $task->getFile($file_meta['id']);
                if (isset($full_file['data'])) {
                    $exploded = explode("base64,", $full_file['data']);
                    $decoded_content = base64_decode(end($exploded));
                    if ($decoded_content) {
                        $csv_files_content[] = $decoded_content;
                    }
                }
            }
        }

        if (empty($csv_files_content)) {
            $this->throwError("No CSV files found for this task.", 404);
            return;
        }

        $merged_csv_data = [];
        $header_written = false;

        foreach ($csv_files_content as $content) {
            $rows = str_getcsv($content, "\n");
            foreach ($rows as $index => $row_string) {
                $row_array = str_getcsv($row_string);
                if (empty(implode('', $row_array))) continue;

                if (!$header_written) {
                    $merged_csv_data[] = $row_array;
                    $header_written = true;
                } else if ($index > 0) {
                    $merged_csv_data[] = $row_array;
                }
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
}
?>
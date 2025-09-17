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
        $task_id = $this->getParam('id');
        if (empty($task_id)) {
            $this->throwError("Task ID not found in URL", 400);
            return;
        }

        $task = LIMSTaskPmm::newFromId($task_id);
        
        $all_files_metadata = $task->files; 

        $csv_files_data = [];

        foreach ((array)$all_files_metadata as $file_meta) {
            $is_csv_by_type = isset($file_meta['type']) && $file_meta['type'] == 'text/csv';
            if ($is_csv_by_type) {
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
                            'timestamp' => $file_meta['created_at'] ?? date('Y-m-d H:i:s') // Use the timestamp from DB
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
            $content = $file_data['content'];
            $assignee_name = $file_data['assignee_name'];
            $timestamp = $file_data['timestamp'];

            $rows = str_getcsv($content, "\n");
            foreach ($rows as $index => $row_string) {
                $row_array = str_getcsv($row_string);
                if (empty(implode('', $row_array))) continue;

                if (!$header_written) {
                    $header_row = $row_array;
                    $header_row[] = 'Assigned';
                    $header_row[] = 'Timestamp';
                    $merged_csv_data[] = $header_row;
                    $header_written = true;
                } else if ($index > 0) {
                    $data_row = $row_array;
                    $data_row[] = $assignee_name;
                    $data_row[] = $timestamp;
                    $merged_csv_data[] = $data_row;
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
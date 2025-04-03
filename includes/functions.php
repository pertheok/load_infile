<?php
    function spacesToCamelCase($string, $capitalizeFirstCharacter = false) 
    {

        $str = str_replace(' ', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }


    /* Auditing functions */
    /**
     * 
     * $part { start/end }
     * 
     */

    function siteAudit($pdo, $part, $action = null, $timeStart = null)
    {
        switch($part)
        {
            case 'start':
                set_time_limit(10000);
                $timeStart = microtime(true); 

                return $timeStart;
            break;
            case 'end':
                if(!$timeStart)
                {
                    return false;
                }

                $timeEnd = microtime(true);
                $executionTime = ($timeEnd - $timeStart);

                //execution time of the script
                echo '<b>Total Execution Time:</b> '.$executionTime.' seconds';

                $scriptPath = $_SERVER['SCRIPT_NAME'];

                $data = [
                    'a_page' => $scriptPath,
                    'a_page_load_time' => $executionTime,
                    'a_action' => $action
                ];

                $sql = 'INSERT INTO site_audit (a_page, a_page_load_time, a_action) VALUES (:a_page, :a_page_load_time, :a_action)';

                $stmt = $pdo->prepare($sql);                
                $stmt->execute($data);
            break;
        }
    }



    /* CSV Functions */

    /*
        $filename { CSV Filename }
        $dataHeaders { CSV Header titles }
        $dataRows { Database dataset Object }
    */
    function createCSV($filename, $dataHeaders, $dataRows) 
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $csvFile = fopen('php://output', 'wb');

        // Add the headers
        fputcsv($csvFile, $dataHeaders);       

        foreach($dataRows as $row)
        {
            $address = '';
            if($row['addressPrimaryAddressableObject']) { $address = $address . ', ' . $row['addressPrimaryAddressableObject']; }
            if($row['addressSecondaryAddressableObject']) { $address = $address . ', ' . $row['addressSecondaryAddressableObject']; }
            if($row['addressFreeDescriptor']) { $address = $address . ', ' . $row['addressFreeDescriptor']; }
            if($row['addressAddressLine1']) { $address = $address . ', ' . $row['addressAddressLine1']; }
            if($row['addressAddressLine2']) { $address = $address . ', ' . $row['addressAddressLine2']; }
            if($row['addressAddressLine3']) { $address = $address . ', ' . $row['addressAddressLine3']; }
            if($row['addressAddressLine4']) { $address = $address . ', ' . $row['addressAddressLine4']; }
            if($row['addressAddressLine5']) { $address = $address . ', ' . $row['addressAddressLine5']; }


            $csvRow = array(
                $row['spid'],
                '0000',
                $row['customerName'],
                $row['MeterReadMinimumFrequency'],
                $row['manufacturerMeterSerialNumber'],
                $address,
                $row['addressPostcode'],
                $row['MeterLocationFreeDescriptor'],

                $row['retailerID'],
                $row['wholesalerID'],
                $row['GPS']
            );

            fputcsv($csvFile, $csvRow);
        }
        
        fclose($csvFile);

        return true;
    }



     /**
     * 
     *  Most pages using the functions will require /wp-load.php
     *      
     * $postType = 'Ticket'
     * 
     */   

     function createTicket(
        $legacy_case_number,
        $legacy_organisation, 
        $legacy_owner_id,
        $legacy_parent_id,
        $legacy_status,
        $legacy_type,

        $post_date,
        $requestor,
        $subject,
        $title,
        $content,
        $new_status,
        $new_ticket_type,
        $type = 'wcrm-tickets') 
     {
        /*

        $ticketNumber = $legacy_case_number . '-SF';
        
         // Insert the ticket activity
         $ticketId = wp_insert_post(array(
             'post_type'     => $type, 
             'post_content'  => $content,

             'publish_date'  => $post_date,
             'post_title'  => $subject,
             'wcrm_tickets_subject' => $subject,

             'wcrm_tickets_requester_email'  => $requestor,

             'post_status'   =>  'publish',
             'legacy_status' => 'Salesforce',
             'legacy_organisation'   =>  $legacy_organisation,
             'legacy_owner_id'   =>  $legacy_owner_id,
             'legacy_parent_id'   =>  $legacy_parent_id,
             'legacy_status'   =>  $legacy_status,
             'legacy_type'   =>  $legacy_type,
             'wcrm_tickets_status' => 'New'
         ));*/

         $ticketId = wp_insert_post(array(
            'post_title'    => $subject,
            'post_type'     => 'wcrm-tickets', 
            'post_content'  => $content,
            'post_status'   => 'publish',
          )); 
 
         // If successfully added
         if($ticketId)
         {                  
            
            $ticketNumber = 'SF-' . $ticketId;
            
            // Update custom fields

            update_field('wcrm_tickets_status', $new_status, $ticketId);            
            update_field('wcrm_tickets_type', $new_ticket_type, $ticketId);

            update_field('wcrm_tickets_ticket_number', $ticketNumber, $ticketId);

            update_field('wcrm_tickets_subject', $subject, $ticketId);
            update_field('wcrm_tickets_requester_email', $requestor, $ticketId);
            update_field('wcrm_tickets_requestor', $requestor, $ticketId);            

            update_field('legacy_case_number', $legacy_case_number, $ticketId);
            update_field('legacy_status', $legacy_status, $ticketId);
            update_field('legacy_organisation', $legacy_organisation, $ticketId);
            update_field('legacy_owner_id', $legacy_owner_id, $ticketId);
            update_field('legacy_parent_id', $legacy_parent_id, $ticketId);
            update_field('legacy_type', $legacy_type, $ticketId);

            update_field('wcrm_tickets_source', 'Salesforce', $ticketId);
            

            echo '<p>updated</p>';
            
             return $ticketId;
         }
         else
         {
             return false;
         }
     }



    /**
     * 
     *  Functions to use in separate Wordpress files
     *  IE Form submission
     *  Most pages using the functions will require /wp-load.php
     *      * 
     * $content
     * activityType = Owner
     * ticketid
     * wcrm_tickets_activity_category = 'audit/task/chat'
     * $postType = 'Ticket Activity'
     * 
     */

    function createTicketActivity($content, $activityType, $ticketId, $activityCategory, $type = 'wcrm_ticket_activity') 
    {
        // Insert the ticket activity
        $ticketActivityId = wp_insert_post(array(
            'post_title'    => $ticketId, 
            'post_type'     => $type, 
            'post_content'  => $content,
            'post_status'   =>  'publish'
        ));

        // If successfully added
        if($ticketActivityId)
        {
            // Update custom fields
            update_field('wcrm_ticket_category', $activityCategory, $ticketActivityId);
            update_field('wcrm_tickets_activity_type', $activityType, $ticketActivityId);
            update_field('wcrm_tickets_activity_ticketid', $ticketId, $ticketActivityId);
            
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * 
     * MDS Functionality
     * 
     */

    function mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, $seperator = null, $lines = 2) 
    {
        $fileName = '../newdata/' . $fileName . '.csv';

        // split by commas
        $dataset = implode(',', $dataset);

        // if not seperator use a comma else
        if(is_null($seperator))
        {
            $seperator = ',';
        }

        // Insert to the apporpriate new upload table and the archive table        
        $query = "LOAD DATA LOCAL INFILE '" . $fileName  . "' INTO TABLE " . $tableName . "
                FIELDS TERMINATED BY '" . $seperator . "' OPTIONALLY ENCLOSED BY '\"' 
                LINES TERMINATED BY '\\n' 
                IGNORE " . $lines . " LINES 
                (
                    " . $dataset . "
                )";

        $pdo->exec($query);
        echo '<p>Inserted to ' . $tableName . '</p>';

        // Insert in to the archive
        $query = "LOAD DATA LOCAL INFILE '" . $fileName  . "' INTO TABLE mds_archive
                FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' 
                LINES TERMINATED BY '\\n' 
                IGNORE 2 LINES 
                (
                " . $dataset . "
                )
                    SET importType = '" . $importType . "'   
                ";

        $pdo->exec($query);
        echo '<p>Inserted to MDS_ARCHIVE</p>';

        return true;
    }
?>
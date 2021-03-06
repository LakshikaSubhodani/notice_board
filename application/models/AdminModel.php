<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class AdminModel extends CI_Model{

    function can_login($username,$password){

        $query = $this->db->query("SELECT * FROM no_user  WHERE user_email ='$username' AND user_status = 'active';");
        $result = $query->result();
        return $result[0];

    }

    //insert data into admin
    public function insert_admin($user_data,$admin_data)
    {
        $this->db->trans_start();

        //insert userdata
        $this->db->insert('no_user', $user_data);
        $user_id = $this->db->insert_id();

        $admin_data['user_Id'] = $user_id;

        //insert admin data
        $this->db->insert('no_user_admin', $admin_data); 
        $admin_id = $this->db->insert_id();       

        // set log
        $this->set_log("Insert new Admin. Id - ". $user_id);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            return false;
        }

        return $admin_id;


    }

    public function update_admin($user_data, $user_id)
    {
        
        $this->db->where('user_Id', $user_id);
        $this->db->update('no_user', $user_data);
        // set log
        $this->set_log("Upadte new Admin. Id - ". $user_id);

    }

    public function get_admin($user_id){

        $query = $this->db->query("SELECT * FROM no_user u LEFT JOIN no_user_admin s ON u.user_Id = s.user_Id WHERE u.user_status = 'active' and u.user_Id = '$user_id'");
        $result = $query->result();
        return $result[0];
    }

    public function check_email_exsit($email)
    {
        $query = $this->db->query("SELECT * FROM no_user WHERE user_email = '".$email."'");
        $row = $query->row();
        
        if(isset($row)){
            return true;
        }
        return false;
    }

    public function check_password_exsit($old_password)
    {
        $query = $this->db->query("SELECT * FROM no_user WHERE user_password = '".$old_password."'");
        $row = $query->row();
        
        if(isset($row)){
            return true;
        }
        return false;
    }


    //get top notices
    public function get_topnotices(){
        $query = $this->db->query("SELECT title,update_date FROM no_notice where notice_status = 'active'  ORDER BY notice_Id DESC LIMIT 7");
        $result = $query->result();
        return $result;
    }
    

    //new notice for database
    public function insertNotice($notice_data,$log_user,$cover_image,$attachments,$links_url,$links_text)
    {
        $this->db->trans_start();

        // insert notice data
        $this->db->insert('no_notice', $notice_data);
        $notice_id = $this->db->insert_id();

        //get admin id
        $this->db->select('admin_Id');
        $this->db->from('no_user_admin');
        $this->db->where('user_Id', $log_user->user_Id);
        $query = $this->db->get();
        $row = $query->row();

        $admin_id =0;

        if (isset($row)){
            $admin_id = $row->admin_Id;
        }

        //insert author data
        $this->db->set('admin_Id', $admin_id);
        $this->db->set('notice_Id', $notice_id);
        $this->db->insert('no_notice_author');

        //insert cover image
        if(!empty($cover_image)){
            foreach ($cover_image as $key => $value) {
            
                $this->db->set('notice_Id', $notice_id);
                $this->db->set('cover_name', $key);
                $this->db->set('cover_url', $value);
                $this->db->insert('no_coverimage');
    
            }        
        }

        //insert attachment
        if(!empty($attachments)){
            foreach ($attachments as $key => $value) {
            
                $this->db->set('notice_Id', $notice_id);
                $this->db->set('attachment_name', $key);
                $this->db->set('attachment_url', $value);
                $this->db->insert('no_attachment');
    
            }        
        }

        //insert links
        if(!empty($links_url) && !empty($links_text)){

            $count = count($links_text);

            for ($i=0; $i < $count ; $i++) { 

                $this->db->set('notice_Id', $notice_id);
                $this->db->set('link_name', $links_text[$i]);
                $this->db->set('link_url', $links_url[$i]);
                $this->db->insert('no_links');

            }
        }
        
        // set log
        $this->set_log("Create new notice. Id - ". $notice_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
              return false;  
        }

        return true;

    }
    
    // get notices for data table
    function get_notices_list($postData=null,$log_user){

        $response = array();

        ## Read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length']; // Rows display per page
        $columnIndex = $postData['order'][0]['column']; // Column index
        $columnName = $postData['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $postData['order'][0]['dir']; // asc or desc
        $searchValue = $postData['search']['value']; // Search value

        ## Search 
        $searchQuery = "";
        if($searchValue != ''){
            $searchQuery = " (notice_Id like '%".$searchValue."%' or title like '%".$searchValue."%' or notice_type like'%".$searchValue."%' ) ";
        }

        ## Total number of records without filtering
        $this->db->select('count(*) as allcount');
        $records = $this->db->get('no_notice')->result();
        $totalRecords = $records[0]->allcount;

        ## Total number of record with filtering
        $this->db->select('count(*) as allcount');
        if($searchQuery != '')
            $this->db->where($searchQuery);
        $records = $this->db->get('no_notice')->result();
        $totalRecordwithFilter = $records[0]->allcount;

        ## Fetch records
        $this->db->select('*');

        $role_id = $log_user->faculty_Id;

        //select query for database;
        $this->db->where('faculty_Id = '.$role_id.' AND notice_Status = "Active"');

        if($searchQuery != '')
        $this->db->where($searchQuery);
        $this->db->order_by($columnName, $columnSortOrder);
        $this->db->limit($rowperpage, $start);
        $records = $this->db->get('no_notice')->result();
        $data = array();

        foreach($records as $record ){

            $data[] = array( 
                "notice_Id"=>$record->notice_Id,
                "title"=>$record->title,
                "update_date"=>$record->update_date,
                "notice_type"=>$record->notice_type,
                "action"=>'
                    <button type="button" class="btn btn-primary btn-xs dt-edit dt-update" data-noticeid="'.$record->notice_Id.'" style="margin-right:16px;">
                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-danger btn-xs dt-delete" data-noticeid="'.$record->notice_Id.'" >
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>'
            ); 
            }

            ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        return $response; 

    }

    public function delete_notice($notice_id){

        $this->db->set('notice_status', 'delete');
        $this->db->set('delete_date', date('Y-m-d H:i:s'));
        $this->db->where('notice_Id', $notice_id);
        $this->db->update('no_notice');

         // set log
         $this->set_log("Delete notice. Id - ". $notice_id);

    }

    //get notice 
    public function get_notice($notice_id){
        $query = $this->db->query("SELECT * FROM no_notice WHERE notice_Id = $notice_id");
        $row = $query->row();
        return $row;
    }
    
    //update notice
    public function update_notice($notice_id,$title,$discription){
        $this->db->set('title', $title );
        $this->db->set('discription',$discription );
        $this->db->where('notice_Id', $notice_id);
        $this->db->update('no_notice');

        // set log
        $this->set_log("Update notice. Id - ". $notice_id);
    }

    //get student record for data table
    public function get_student_list($postData = null,$log_user){
        $response = array();

        ## Read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length']; // Rows display per page
        $columnIndex = $postData['order'][0]['column']; // Column index
        $columnName = $postData['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $postData['order'][0]['dir']; // asc or desc
        $searchValue = $postData['search']['value']; // Search value

         ## Search 
         $searchQuery = "";
         if($searchValue != ''){
             $searchQuery = " (user_Id like '%".$searchValue."%' or enrollment_Id like '%".$searchValue."%' or user_firstname like '%".$searchValue."%' or user_email like'%".$searchValue."%' ) ";
         }

         ## Total number of records without filtering
        $this->db->select('count(*) as allcount');
        $records = $this->db->get('no_student_view')->result();
        $totalRecords = $records[0]->allcount;

         ## Total number of record with filtering
         $this->db->select('count(*) as allcount');
         if($searchQuery != '')
             $this->db->where($searchQuery);
         $records = $this->db->get('no_student_view')->result();
         $totalRecordwithFilter = $records[0]->allcount;

          ## Fetch records
        $this->db->select('*');

        $role_id = $log_user->faculty_Id;

        //select query for database;
        $this->db->where('user_status = "Active"');

        if($searchQuery != '')
        $this->db->where($searchQuery);
        $this->db->order_by($columnName, $columnSortOrder);
        $this->db->limit($rowperpage, $start);
        $records = $this->db->get('no_student_view')->result();
        $data = array();

        foreach($records as $record ){

            $data[] = array( 
                "user_Id"=>$record->user_Id,
                "enrollment_Id"=>$record->enrollment_Id,
                "user_firstname"=>$record->user_firstname,
                "user_lastname"=>$record->user_lastname,
                "user_email"=>$record->user_email,
                "faculty"=>$record->faculty,
                "action"=>'
                
                <button type="button" class="btn btn-danger btn-xs dt-delete" data-studentid="'.$record->user_Id.'" >
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>'
            ); 
            }

             ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        return $response; 
 
    }

    //delete student
    public function delete_student($student_id){
        $this->db->set('user_status', 'delete');
        $this->db->where('user_Id', $student_id);
        $this->db->update('no_user');

        // set log
        $this->set_log("Delete Student. Id - ". $student_id);
    }

    //get admin record for data table
    public function get_admin_list($postData = null,$log_user){
        $response = array();

         ## Read value
         $draw = $postData['draw'];
         $start = $postData['start'];
         $rowperpage = $postData['length']; // Rows display per page
         $columnIndex = $postData['order'][0]['column']; // Column index
         $columnName = $postData['columns'][$columnIndex]['data']; // Column name
         $columnSortOrder = $postData['order'][0]['dir']; // asc or desc
         $searchValue = $postData['search']['value']; // Search value

          ## Search 
          $searchQuery = "";
          if($searchValue != ''){
              $searchQuery = " (user_Id like '%".$searchValue."%' or faculty like '%".$searchValue."%' or user_firstname like '%".$searchValue."%' or user_email like'%".$searchValue."%' ) ";
          }

           ## Total number of records without filtering
        $this->db->select('count(*) as allcount');
        $records = $this->db->get('no_admin_view')->result();
        $totalRecords = $records[0]->allcount;

         ## Total number of record with filtering
         $this->db->select('count(*) as allcount');
         if($searchQuery != '')
             $this->db->where($searchQuery);
         $records = $this->db->get('no_admin_view')->result();
         $totalRecordwithFilter = $records[0]->allcount;

           ## Fetch records
        $this->db->select('*');

        $role_id = $log_user->faculty_Id;

        //select query for database;
        $this->db->where('user_status = "Active"');

        if($searchQuery != '')
        $this->db->where($searchQuery);
        $this->db->order_by($columnName, $columnSortOrder);
        $this->db->limit($rowperpage, $start);
        $records = $this->db->get('no_admin_view')->result();
        $data = array();


        foreach($records as $record ){

            $data[] = array( 
                "user_Id" => $record->user_Id,
                "user_firstname"=>$record->user_firstname,
                "user_lastname"=>$record->user_lastname,
                "user_email"=>$record->user_email,
                "faculty"=>$record->faculty,
                "action"=>'
                
                <button type="button" class="btn btn-danger btn-xs dt-delete" data-adminid="'.$record->user_Id.'" >
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>'
            ); 
            }
 
                 ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        return $response; 
    }

    public function delete_admin($admin_id){
        $this->db->set('user_status', 'delete');
        $this->db->where('user_Id', $admin_id);
        $this->db->update('no_user');

        // set log
        $this->set_log("Delete Admin. Id - ". $admin_id);
    }


    // insert sys logs

    public function set_log($activity){
         
        $log_user = $this->session->userdata('log_user');

        $this->db->set('activity', $activity);
        $this->db->set('user_id', $log_user->user_Id);
        $this->db->insert('no_system_log');
    }

    public function get_log(){

        $query = $this->db->query("SELECT s.system_log_Id, s.activity, u.user_firstname, u.user_lastname, s.date  FROM no_system_log s INNER JOIN no_user u ON s.user_Id = u.user_Id  ORDER BY system_log_Id DESC LIMIT 10");
        $result = $query->result();
        return $result;

        return false;
    }
}

?>
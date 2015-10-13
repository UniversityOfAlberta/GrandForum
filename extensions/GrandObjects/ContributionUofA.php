<?php

    /**
     * @package GrandObjects
     */
    class ContributionUofA {
	
	var $id;
	var $project_id;
	var $user_id;
	var $title;
	var $description;
	var $start_date;
	var $end_date;
	var $user_role;
	var $team;
        var $keywords = array();
	var $request;
	var $available_funds_before;
	var $available_funds_after;
	var $total_award;
	var $speed_code;
	var $overexpenditure_status;
	var $sponsor;
        var $sponsor_program;
        var $project_type;
        var $project_status;
        var $percent_spent;
        var $award_num;
        var $change_date;

	function ContributionUofA($data){
            $this->id = $data[0]['id'];
            $this->project_id = $data[0]['project_id']
            $this->user_id = $data[0]['user_id']
            $this->title = $data[0]['title']
            $this->description = $data[0]['description']
            $this->start_date = $data[0]['start_date']
            $this->end_date = $data[0]['end_date']
            $this->user_role = $data[0]['user_role']
            $this->team = $data[0]
            $this->keywords = array() = $data[0]
            $this->request = $data[0]
            $this->available_funds_before = $data[0]
            $this->available_funds_after = $data[0]
            $this->total_award = $data[0]
            $this->speed_code = $data[0]
            $this->overexpenditure_status = $data[0]
            $this->sponsor = $data[0]
            $this->sponsor_program = $data[0]
            $this->project_type = $data[0]
            $this->project_status = $data[0]
            $this->percent_spent = $data[0]
            $this->award_num = $data[0]
            $this->change_date = $data[0]
	}
















    }

?>

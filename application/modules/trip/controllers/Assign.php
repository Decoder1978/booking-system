<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assign extends MX_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model(array(
			'assign_model',
			'route_model'
		));		 
	}
 
	public function index()
	{   
        $this->permission->method('trip','read')->redirect();
		$data['title'] = display('list'); 
		#-------------------------------#
		#
        #pagination starts
        #
        $config["base_url"] = base_url('trip/assign/index');
        $config["total_rows"] = $this->db->count_all('trip_assign');
        $config["per_page"] = 25;
        $config["uri_segment"] = 4;
        $config["last_link"] = "Last"; 
        $config["first_link"] = "First"; 
        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Prev';  
        $config['full_tag_open'] = "<ul class='pagination col-xs pull-right'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";
        /* ends of bootstrap */
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        $data["assigns"] = $this->assign_model->read($config["per_page"], $page);
        $data["links"] = $this->pagination->create_links();
        #
        #pagination ends
        #  
		$data['module'] = "trip";
		$data['page']   = "assign/list";   
		echo Modules::run('template/layout', $data);  
	}  

 	public function form($id = null)
	{ 
		$data['title'] = display('add');
		#-------------------------------#
		$this->form_validation->set_rules('fleet_registration_id',display('fleet_registration_no')  ,'required|max_length[11]');
		
		$this->form_validation->set_rules('driver_id',display('driver_name')  ,'required|max_length[11]');
		$this->form_validation->set_rules('status',display('status') ,'required');	
		/*-----------------------------------*/
		$ids = $this->input->post('id');
		$id_no = (!empty($ids)?$this->input->post('id_no'):$this->randStrGen());
		$start_date = $this->input->post('start_date'); 
		$end_date   = $this->input->post('end_date'); 
		$assigndate = $this->input->post('assign_date');
		//$trip = (!empty($this->input->post('trip'))?$this->input->post('trip'):0);
		#-------------------------------#
		$data['assign'] = (Object) $postData = [
			'id' 	 			 => $ids, 
			'id_no' 	 		 => $id_no, 
			'fleet_registration_id' => $this->input->post('fleet_registration_id'), 
			'driver_id'          => $this->input->post('driver_id'), 
			'assistant_1'        => $this->input->post('assistant_1'), 
			'assistant_2'        => $this->input->post('assistant_2'), 
			'assistant_3'        => $this->input->post('assistant_3'), 
			'status'             => $this->input->post('status'),
			'assign_time'        => date('Y-m-d H:i:s', strtotime((!empty($assigndate)?$assigndate:date('Y-m-d H:i:s')))),
			'trip'               => $this->input->post('trip'),
			'date'               => 'NULl',
		]; 

           $driverinfo =[
                'is_assign' => 1,
                ];

                 $assistant1 =[
                'is_assign' => 1,
                ];
                $assistant2 =[
               'is_assign' => 1,
                ];
             $assistant3 =[
                'is_assign' => 1,
                ];
                $fleetinfo =[
                'is_assign' => 1,
                ];

		#-------------------------------#
		if ($this->form_validation->run()) { 
			//print_r($postData);exit;

			if (empty($postData['id'])) {

        		$this->permission->method('trip','create')->redirect();

				if ($this->assign_model->create($postData)) { 
					$this->db->where('id',$this->input->post('driver_id'))
			               ->update('employee_history',$driverinfo);
			         if(!empty($this->input->post('assistant_1'))){
			               	$this->db->where('id',$this->input->post('assistant_1',true))
                    ->update('employee_history',$assistant1);
			               } 
			               if(!empty($this->input->post('assistant_2'))){
			               	$this->db->where('id',$this->input->post('assistant_2',true))
                    ->update('employee_history',$assistant2);
			               } 
			                if(!empty($this->input->post('assistant_3'))){
			               	$this->db->where('id',$this->input->post('assistant_3',true))
                    ->update('employee_history',$assistant3);
			               } 
                if(!empty($this->input->post('fleet_registration_id'))){
			    $this->db->where('id',$this->input->post('fleet_registration_id',true))
               ->update('fleet_registration',$fleetinfo);
			               } 
					$this->session->set_flashdata('message', display('save_successfully'));
				} else {
					$this->session->set_flashdata('exception',  display('please_try_again'));
				}
				redirect("trip/assign/form"); 

			} else {

        		$this->permission->method('trip','update')->redirect();

				if ($this->assign_model->update($postData)) { 
					 if(!empty($this->input->post('fleet_registration_id'))){
			    $this->db->where('id',$this->input->post('fleet_registration_id',true))
               ->update('fleet_registration',$fleetinfo);
			               } 
					$this->session->set_flashdata('message', display('update_successfully'));
				} else {
					$this->session->set_flashdata('exception',  display('please_try_again'));
				}
				redirect("trip/assign/form/".$postData['id']);  
			}
 

		} else { 
			if(!empty($id)) {
			$data['title'] = display('update');
		    $data['assign']   = $this->assign_model->findById($id);
		    $data['fleet_dropdown'] = $this->assign_model->fleet_dropdown_update();
		    $data['driver_dropdown'] = $this->assign_model->driver_dropdown_update();
		    $data['assistant_dropdown'] = $this->assign_model->assistant_dropdown_update();
			}
			if(empty($id)) {
			$data['fleet_dropdown'] = $this->assign_model->fleet_dropdown();
			$data['driver_dropdown'] = $this->assign_model->driver_dropdown();
			$data['assistant_dropdown'] = $this->assign_model->assistant_dropdown();
		    }
			$data['route_dropdown'] = $this->route_model->dropdown();
			$data['trip'] = $this->assign_model->trip_dropdown();
			$data['shedule'] = $this->assign_model->shedule_dropdown();
			$data['module'] = "trip";
			$data['page']   = "assign/form";   
			echo Modules::run('template/layout', $data); 
		}   
	}

	public function view($id_no = null) 
	{ 
        $this->permission->method('trip','read')->redirect();
        $data['title'] = display('assign');
		$data['assign'] = $this->assign_model->findByIdNo($id_no);
		$data['module'] = "trip";
		$data['page']   = "assign/view";   
		echo Modules::run('template/layout', $data); 
	}

 

	public function delete($id = null) 
	{ 
        $this->permission->method('trip','delete')->redirect();

		if ($this->assign_model->delete($id)) {
			#set success message
			$this->session->set_flashdata('message',display('delete_successfully'));
		} else {
			#set exception message
			$this->session->set_flashdata('exception',display('please_try_again'));
		}
		redirect('trip/assign/index');
	}
	 


    /*
    |----------------------------------------------
    |        id genaretor
    |----------------------------------------------     
    */
    public function randStrGen()
    {
        return date('ymdhis');
    }
    /*
    |----------------------------------------------
    |         Ends of id genaretor
    |----------------------------------------------
    */
	
}
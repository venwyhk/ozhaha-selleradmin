<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class add_item_category extends CI_Controller {

	/**
	 * author:dmh describe:
	 * 
	 */
	public function index()
	{
		$this->load->library('parser');				
		$this->load->library('session');
		$this->load->library('form_validation');		
		$this->load->helper('language');
		$this->load->helper('file');
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->library('waimai_seller');
		$seller_id = $this->waimai_seller->check_login();
		$this->isloadtemplate = 0;		
		$data = array(
		    'static_base_url' => $this->config->item('static_base_url'),
		    'seller_name' => $this->session->seller_name,
		    'logo_url' => ($this->cache->get('logo_url'.$seller_id)!='uploads/')?($this->cache->get('logo_url'.$seller_id)):"",
		);
		$data['validation_errors'] = '';
		$data['result_success'] = '';
		
		$post_data = $this->input->post('data[]', true);
		$this->load->database();
		if (empty($post_data))
		{
				$data['post_data[name]'] = '';
				$this->isloadtemplate = 1;
		}else
		{
				$this->lang->load('add_item_category');
		 		$this->form_validation->set_rules('data[name]', lang('add_item_category_name'), 'required');
		 				 				
		 		if ($this->form_validation->run() == FALSE)
		    {
		    		$data['validation_errors'] = validation_errors();
						$this->isloadtemplate = 1;
		    }else
		    {
		    		//����д��
		    		$query = $this->db->query("SELECT count(*) as total FROM ".$this->db->dbprefix('item_category')." WHERE seller_id='$seller_id' and name=".$this->db->escape($post_data['name']));
						$row = $query->row_array();
						if (!$row['total'])
						{
								$d = array(
								    'gmt_create' => date('Y-m-d H:i:s',time()),
								    'name' => $post_data['name'],
								    'seller_id' => $seller_id		   						   
								);
								$query = $this->db->query("SELECT count(*) as total FROM ".$this->db->dbprefix('item_category')." WHERE seller_id='$seller_id'");
								$row = $query->row_array();
								if ($row['total']) $d['parent_id'] = 0;
								$this->db->insert($this->db->dbprefix('item_category'),$d);
								$data['post_data[name]'] = '';								
								$data['result_success'] = lang('add_item_category_success_1');
								$this->isloadtemplate = 1;
						}else
						{
								$data['post_data[name]'] = $post_data['name'];
								$data['validation_errors'] = lang('add_item_category_error_1');
								$this->isloadtemplate = 1;
						}
		    }
		}
		$this->db->close();
		if($this->isloadtemplate)
		{
				$this->parser->parse('add_item_category_template', $data);
		}
	}
	
}

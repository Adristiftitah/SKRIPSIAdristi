<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MahasiswaController extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		if ($this->session->userdata('email')=="") {
			redirect('LoginController','refresh');	
		}
		$this->load->model('model_user');
 		$this->load->model('AdminModels');
		$this->load->helper('text');
	}

	public function index()
	{
		$data['email'] = $this->session->userdata('email');
		$data['tampilan_mahasiswa'] = "Mahasiswa/Dashboard";
		$this->load->view('Mahasiswa/Tview',$data);

	}

	public function logout()
	{
		$this->session->unset_userdata('email');
		$this->session->unset_userdata('level');
		session_destroy();
		redirect('LoginController');
	}

	public function addproposal()
	{
		$data['tampilan_mahasiswa'] = "Mahasiswa/PengajuanProposal";
		$id = $this->session->userdata('id_users');
		$user = $this->AdminModels->getmahasiswa($id)->row();
		// var_dump($user);
		// die();
		$data['proposal'] = $this->AdminModels->pengajuan_admin($id);
		$data['dosen'] = $this->AdminModels->getdosen(null)->result();
		$data['perusahaan'] = $this->AdminModels->getPerusahaan();
		$data['mahasiswa'] = $user;
		
		$this->load->view('Mahasiswa/Tview',$data);
	}

	public function insproposal()
	{
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'pdf';
		$config['max_size']  = '3000';
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload', $config);  //File Uploading library
		$this->upload->do_upload('file');  // input name which have to upload 
		$file_pengajuan=$this->upload->data('file_name');   //variable which store the path

		$config2['upload_path'] = './uploads/mou/';
		$config2['allowed_types'] = 'pdf';
		$config2['max_size']  = '3000';
		$config2['encrypt_name'] = TRUE;
		$this->upload->initialize($config2);
		$this->upload->do_upload('file_mou');
		$file_mou = $this->upload->data('file_name');

		$config2['upload_path'] = './uploads/spk/';
		$config2['allowed_types'] = 'pdf';
		$config2['max_size']  = '3000';
		$config2['encrypt_name'] = TRUE;
		$this->upload->initialize($config2);
		$this->upload->do_upload('file_spk');
		$file_spk = $this->upload->data('file_name');
		
		
		if ( ! $this->upload->do_upload('file')){
			$error = array('error' => $this->upload->display_errors());
			echo $this->upload->display_errors();
		}else if ( ! $this->upload->do_upload('file_mou')){
			$error = array('error' => $this->upload->display_errors());
			echo $this->upload->display_errors();
		}else if ( ! $this->upload->do_upload('file_spk')){
			$error = array('error' => $this->upload->display_errors());
			echo $this->upload->display_errors();
		}
		else{
			$id = $this->session->userdata('id_users');

			$user = $this->AdminModels->getmahasiswa($id)->row();
			
			$data = array(
				'mahasiswa_id' => $user->id_mahasiswa,
				'kodeprodi' => $user->kodeprodi,
				'namaAng1' => $this->input->post('anggota1'),
				'namaAng2' => $this->input->post('anggota2'),
				'nimAng1' => $this->input->post('nim1'),
				'nimAng2' => $this->input->post('nim2'),
				'prodi' => $this->input->post('prodi'),
				'id_perusahaan' => $this->input->post('id_perusahaan'),
				'tanggalMulai' => $this->input->post('durasi'),
				'tanggalAkhir' => $this->input->post('exp_durasi'),
				'file_pengajuan' => $file_pengajuan,
				'file_mou'=> $file_mou,
				'file_spk'=> $file_spk,
				'create_at' => date('Y-m-d')
			);
			$this->AdminModels->ins('pengajuan_admin',$data);
			redirect('MahasiswaController/addproposal');
		}
	}

	public function addpembimbing()
	{
		$data['tampilan_mahasiswa'] = "Mahasiswa/PengajuanPembimbing";
		$id = $this->session->userdata('id_users');
		$data['nim'] = $this->AdminModels->getmahasiswa($id)->row()->nim;
		$data['dosen'] = $this->AdminModels->getdosen(null)->result();
		$data['pembimbing'] = $this->AdminModels->pengajuan_pembimbing($data['nim']);
		
		$this->load->view('Mahasiswa/Tview',$data);
	}


	public function inspembimbing()
	{
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'pdf';
		$config['max_size']  = '3000';

		$this->load->library('upload', $config);
		
		if ( ! $this->upload->do_upload('file')){
			$error = array('error' => $this->upload->display_errors());
			echo $this->upload->display_errors();
		}
		else{
			$id = $this->session->userdata('id_users');
			
			$user = $this->AdminModels->getmahasiswa($id)->row();
			$nim2 = $this->input->post('nim1');
			$nim3 = $this->input->post('nim2');
			$data = array(
				'nim' => $this->input->post('nim'),
				'dosen_id' => $this->input->post('dosen'),
				'file_pengajuan' => $this->upload->data('file_name'),
				'status' => 'diproses',
				'create_at' => date('Y-m-d')
			);
			if ($nim2 != null) {
				$data['nim2'] = $nim2;
			}

			if($nim3 != null){
				$data['nim3'] = $nim3;
			}
			
			$this->AdminModels->ins('pengajuan_pembimbing',$data);
			redirect('MahasiswaController/addpembimbing');
		}
	}

	public function inPerusahaan()
	{
		$data['tampilan_mahasiswa'] = "Mahasiswa/PerusahaanInfo";
		$data['perusahaan'] = $this->AdminModels->perusahaan();
		$this->load->view('Mahasiswa/Tview',$data);

	}

	public function downloadMou($id)
	{
		$this->db->where('id_pengajuan', $id);
		$this->db->from('pengajuan_admin');
		$query = $this->db->get();
		$nama_file = $query->row()->file_mou;
		force_download('uploads/mou/'.$nama_file, NULL);
	}

	public function downloadSpk($id)
	{
		$this->db->where('id_pengajuan', $id);
		$this->db->from('pengajuan_admin');
		$query = $this->db->get();
		$nama_file = $query->row()->file_spk;
		force_download('uploads/spk/'.$nama_file, NULL);
	}

	public function updateFileProposal()
	{
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'pdf';
		$config['max_size']  = '3000';
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload', $config);  //File Uploading library
		$this->upload->do_upload('file');  // input name which have to upload 
		$file_pengajuan=$this->upload->data('file_name');   //variable which store the path

		$config2['upload_path'] = './uploads/mou/';
		$config2['allowed_types'] = 'pdf';
		$config2['max_size']  = '3000';
		$config2['encrypt_name'] = TRUE;
		$this->upload->initialize($config2);
		$this->upload->do_upload('file_mou');
		$file_mou = $this->upload->data('file_name');

		$config2['upload_path'] = './uploads/spk/';
		$config2['allowed_types'] = 'pdf';
		$config2['max_size']  = '3000';
		$config2['encrypt_name'] = TRUE;
		$this->upload->initialize($config2);
		$this->upload->do_upload('file_spk');
		$file_spk = $this->upload->data('file_name');
		
	
		$id = $this->session->userdata('id_users');
		$id_pengajuan = $this->input->post('id_pengajuan');
		if($this->upload->do_upload('file') != null)
		{
			$this->db->where('id_pengajuan', $id_pengajuan);
			$this->db->set('file_pengajuan', $file_pengajuan);
	
		}
		if($this->upload->do_upload('file_mou') !=null)
		{
			$this->db->where('id_pengajuan', $id_pengajuan);
			$this->db->set('file_mou', $file_mou);
			

		}
		if($this->upload->do_upload('file_spk') !=null)
		{
			$this->db->where('id_pengajuan', $id_pengajuan);
			$this->db->set('file_spk', $file_spk);

		}
		$this->db->update('pengajuan_admin');
		redirect('MahasiswaController/addproposal');
		
		
	}

	public function downloadTemplate($id)
	{
		$filename = null;
		
		if($id == 1)
		{
			$filename = "FORM_BIMBINGAN_PKL_(DOSEN)_D3.docx";
		}else if($id == 2)
		{
			$filename = "FORM BIMBINGAN PKL (DOSEN)_D4.docx";
		}else if($id == 3)
		{
			$filename = "FORM BIMBINGAN PKL (INSTANSI).docx";
		}else if($id == 4)
		{
			$filename = "FORM PENILAIAN PEMBIMBING PKL D4.docx";
		}
		else if($id == 5)
		{
			$filename = "Draft Permohonan Pengantar PKL.docx";
		}
		else if($id == 6)
		{
			$filename = "(MI) Tanda Terima Penyerahan Laporan PKL-Magang.docx";
		}
		else if($id == 7)
		{
			$filename = "(TI) Tanda Terima Penyerahan Laporan PKL-Magang.docx";
		}
		force_download('assets/template/'.$filename, NULL);
	}


}

/* End of file MahasiswaController.php */
/* Location: ./application/controllers/MahasiswaController.php */
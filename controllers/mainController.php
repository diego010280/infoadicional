<?php
require_once 'core/Controller.php';
require_once 'models/DatosCuenta.php';
require_once 'models/Menu.php';
//require_once 'models/ooddModel.php';

class MainController extends Controller {
	const SIZE_MAX = 10485760;
	const ALLOW_EXT = array("jpg","jpeg","png","pdf");
	const ALLOW_TYPE = array("application/pdf","image/jpeg","image/jpg","image/png");
	private $inicio = 0;
	private $pageSize = 9;
	
	public function processData($data) {
		$sanitizeData = array_map(function($value) {
			return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
		}, $data);
		
		return $sanitizeData;
	}
	
	public function menuPerfil() {
		$cuenta = new Cuenta();
		$datperfil = $cuenta->getPerfil($_SESSION['usuario_id']);
		
		include 'views/menuPerfil.php';
	}
	
	/* public function inicio($allowed) {
		$posts = '';
		
		$cuenta = new Cuenta();
		$users = $cuenta->getPerfil($_SESSION['usuario_id']);
		
		$menu = new Menu();
		$options = $menu->getUserMenu($_SESSION['usuario_id'], $_SESSION['moduleid']);
		
		$ooddmodel = new ooddModel();
		$oodd = $ooddmodel->getListOodd($this->inicio, $this->pageSize);
		$num_oodd = $oodd->num_rows;
		
		$num_total = $ooddmodel->getNumOodd();
		$temas = $ooddmodel->getTemas();
		
		require_once 'views/plantilla.php';
	}
	
	public function searchResults($allowed, $page, $data = []) {
		
		$ooddmodel = new ooddModel();
		$this->inicio = $data['posi'] ?? 0;
		
		if ($page == 'oodd.php') {
			
			if (!empty($data['posts'])) {
				$data = unserialize(base64_decode($data['posts']));
				$data['posi'] = $this->inicio;
			}
			
			if (empty($data) || !isset($data['nroOrden'])) {
				$oodd = $ooddmodel->getListOodd($this->inicio, $this->pageSize);
				$num_total = $ooddmodel->getNumOodd();
			
			} else {
				$oodd = $ooddmodel->searchOodd($data, $this->inicio, $this->pageSize);
				$num_total = $ooddmodel->getNumSearch($data)['cantoodd'];
			}
			$num_oodd = $oodd->num_rows;
		
		} elseif ($page == 'temas.php') {
			if (empty($data)) {
				$num_tema = 0;
			
			} else {
				$tema = $ooddmodel->getTemaId(explode(' ', $data['b_temas'])[0]);
				$num_tema = $tema->num_rows;
			}
		}
		$posts = !empty($data) ? base64_encode(serialize($data)) : '';
		
		require_once 'views/'.basename($page, '.php').'_body.php';
	}
	
	public function crud($allowed, $page, $data = []) {
		
		$ooddmodel = new ooddModel();
		$temas = $ooddmodel->getTemas();
		
		if ($page == 'oodd.php') {
			
			if (!empty($data['posts'])) {
				$posts  = $data['posts'];
				$data = unserialize(base64_decode($posts));
				$this->inicio = $data['posi'] ?? 0;
				
				if (isset($data['posi']) && !isset($data['nroOrden'])) {
					$oodd = $ooddmodel->getListOodd($this->inicio, $this->pageSize);
					$num_total = $ooddmodel->getNumOodd();
					$num_oodd = $oodd->num_rows;
					
				} else {
					$oodd = $ooddmodel->searchOodd($data, $this->inicio, $this->pageSize);
					$num_total = $ooddmodel->getNumSearch($data)['cantoodd'];
					$num_oodd = $oodd->num_rows;
				}
			} else {
				$posts = '';
				$oodd = $ooddmodel->getListOodd($this->inicio, $this->pageSize);
				$num_oodd = $oodd->num_rows;
				$num_total = $ooddmodel->getNumOodd();
			}
		
		} elseif (in_array($page,['oodd_a.php','oodd_m.php'])) {
			
			if ($page == 'oodd_m.php') {
				$oodd = $ooddmodel->getOoddId($_POST);
				$row_oodd = $oodd->fetch_assoc();
			}
			
			$tipoentidad = $ooddmodel->getEntidades();
			$juzgados = $ooddmodel->getJuzgados();
			$desti = $ooddmodel->getDependencias();
			$otros = $ooddmodel->getOtrosOrg();
			$anio_max = date('Y');
		
		} elseif ($page == 'oodd_b.php') {
			$oodd = $ooddmodel->getOoddId($_POST);
			$row_oodd = $oodd->fetch_assoc();
		
		} elseif ($page == 'temas.php') {
			if (!empty($data['posts'])) {
				$posts  = $data['posts'];
				$data = unserialize(base64_decode($posts));
				$tema = $ooddmodel->getTemaId(explode(' ', $data['b_temas'])[0]);
				$num_tema = $tema->num_rows;
			
			} else {
				$posts = '';
			}
			$temas = $ooddmodel->getTemas();
			
		} elseif (in_array($page, ['temas_m.php','temas_b.php'])) {
			$tema = $ooddmodel->getTemaId($data['temas_id']);
			$row_tema = $tema->fetch_assoc();
		}
		require_once "views/$page";
	}
	
	public function verify($data, $metodo) {
		$ooddmodel = new ooddModel();
		$resultado = $ooddmodel->$metodo(explode(' ', $data)[0]);
		
		if ($resultado->num_rows > 0) {
			return true;
		}
		return false;
	}
	
	public function save($page, $data) {
		$data = $this->processData($_POST);
		$ooddmodel = new ooddModel();
		
		if (in_array($page,['oodd_a.php', 'oodd_m.php'])) {
			switch ($data['entidad_tipo']) {
				case 2: $iniciador = [$data['juzgado'], 'getJuzgadoId']; break;
				case 3: $iniciador = [$data['dependencia'], 'getDependenciaId']; break;
				case 4: $iniciador = [$data['otros'], 'getOtrosOrgId']; break;
			}
			$resultado = 6;
			
			if ($this->verify($data['oodd_temas_id'], 'getTemaId')) {
				if ($this->verify($iniciador[0], $iniciador[1])) {
					if ($this->verify($data['solicitante_codigo'], 'getDependenciaId')) {
						switch ($data['operacion']) {
							case 'regnew':
								$resultado = $ooddmodel->newOodd($data, $iniciador[0]);
								break;
							case 'regmodif':
								$resultado = $ooddmodel->updateOodd($data, $iniciador[0]);
								break;
						}
					}
				}
			}
		
		} elseif ($page == 'agregar_pdf.php') {

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = strtolower(finfo_file($finfo, $_FILES['pdf_a_cargar']['tmp_name']));
			$extFile = strtolower(pathinfo($_FILES['pdf_a_cargar']['name'],PATHINFO_EXTENSION));
			
			if (in_array($mime, self::ALLOW_TYPE) && in_array($extFile, self::ALLOW_EXT)) {
				if ($_FILES['pdf_a_cargar']['size'] > self::SIZE_MAX) {
					$resultado = 8;

				} else {
					$nombrefoto = $data['oodd_anio'].'_'.$data['oodd_nro'].'_'.$data['oodd_temas_id'].'.pdf'; 
					$destino = 'oodd_pdf/'.$nombrefoto;
					
					if (move_uploaded_file($_FILES["pdf_a_cargar"]["tmp_name"], $destino)) {
						$resultado = $ooddmodel->savePdf($data, $destino);
					
					} else {
						$resultado = 6;
					}
				}
			} else {
				$resultado = 7;
			}
		
		} elseif ($page == 'oodd_b.php') {
			$resultado = 6;
			
			if (empty($data['deletedata'])) {
				$resultado = 5;
			
			} elseif ($data['deletedata'] == 1) {
				$resultado = $ooddmodel->deleteOodd($data);
			
			} elseif ($data['deletedata'] == 2) {
				$resultado = $ooddmodel->deletePdf($data);
			}
			if ($resultado == 3 && file_exists($data['oodd_ruta_pdf'])) {
				unlink($data['oodd_ruta_pdf']);
			}
		
		} elseif (in_array($page,['temas_a.php','temas_m.php','temas_b.php'])) {
			switch ($data['operacion']) {
				case 'regnew':
					$resultado = $ooddmodel->newTema($data);
					break;
				case 'regborrar':
					$resultado = $ooddmodel->deleteTema($data);
					break;
				case 'regmodif':
					$resultado = $ooddmodel->updateTema($data);
					break;
			}
		}
		echo json_encode(array('accion' => $resultado));
	} */
}
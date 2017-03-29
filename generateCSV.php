<?php

class GenerateCSV
{

	public $filename;
	public $tmp_dir;
	public $header;
	public $content;
	public $file;
	public $filenameComplete;
	public $rels;


	function __construct()
	{
		$params = func_get_args();

		$num_params = func_num_args();
		$funcion_constructor ='__construct'.$num_params;
		if (method_exists($this,$funcion_constructor)) {

			call_user_func_array(array($this,$funcion_constructor),$params);
		}

	}
	function __construct2($filename , $rels)
	{
		$this->filename = $filename;
		$this->file = ObjectFactory::invokeStatic(ObjectFactory::File);
		$this->rels = $rels;
	}

	function __construct3($filename ,$header , $content )
	{
		$this->filename = $filename;
		$this->header = $header;
		$this->content = $content;
		$this->file = ObjectFactory::invokeStatic(ObjectFactory::File);
	}

	public function createFile( $extension = 'csv' ){

		$this->tmp_dir = $this->file->createTempDir();
		$text =  $this->header . $this->content;
		$this->filenameComplete = $this->tmp_dir.'/'.$this->filename . '.' . $extension;
		file_put_contents($this->filenameComplete , $text);

	}

	public function downloadFile(){
		if ($this->rels) $this->proccessQuery();
		$this->createFile();

		$this->file->getTempFile($this->filenameComplete, $this->tmp_dir);
			if (!file_exists($this->filenameComplete)) {
				FWUtils::log(BlinkFW_LogFile::LEVEL_DEBUG, "No existe el fichero temporal " . $this->filenameComplete);
				$this->deleteFiles();
				sendError(textweb(array("name"=>"editclass.error_getting_file", "value"=>" Error obteniendo archivo temporal %s", "args"=>array($this->filenameComplete))));
			}else{
				header('Expires: 0');
			    header('Cache-Control: must-revalidate');
			    header('Pragma: public');
			    header('Content-Length: ' . filesize($this->filenameComplete));
			    header('Content-Disposition: attachment; filename='.basename($this->filenameComplete));
			    readfile($this->filenameComplete);
				$this->deleteFiles();
			    exit;
			}
	}


	public function deleteFiles(){
		$this->file->deleteTempFile($this->filename);
		$this->file->deleteDir($this->tmp_dir);
	}

	public function proccessQuery(){
		$unaVez = true;
		foreach ($this->rels as $filas) {
			$lengtFilas = count($filas);
			$count = 1;
			foreach ($filas as $key => $value) {
				$finalFila = $lengtFilas == $count;
				$value = $value == null ? 'NULL': $value;
					if (gettype($key) == 'string' ){
						if($unaVez) {
							$this->header .= $key ."\t";
						}
						$this->content .= $value . "\t";

						if ($finalFila){
							if($unaVez) $this->header .= "\r\n";
							$this->content .=  "\r\n";
							$unaVez = false;
						}
					}
				$count++;
			}
		}
	}
}

 ?>

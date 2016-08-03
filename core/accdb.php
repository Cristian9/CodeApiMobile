<?php

class accdb extends mysqli{

	private $host = "localhost";
	private $user = "root";
	private $pass = "";
	private $ddbb = "db_preguntados";
	protected $cn;

	public function __construct(){
		$this->cn = parent::connect($this->host, $this->user, $this->pass, $this->ddbb);

		if($this->connect_errno){
			die('Hubo un error # ' . $this->connect_errno . ': ' . $this->connect_error);
		}
	}

	public function dataSet($sql){
		$query = $this->query($sql);
		$tbl = $query->fetch_assoc();

		$data = array();
		do{
			$data[] = $tbl;
		}while($tbl = $query->fetch_assoc());

		return $data;
	}

	public function execQuery($sql){
		return $this->query($sql) ? true : false;
	}

	public function InsertAndGetLastId($sql) {
		$query = $this->query($sql);
		return $this->insert_id;
	}

	public function numRows($sql){
		$query = $this->query($sql);
		return $query->num_rows;
	}

	function __destruct(){
		$this->close();
		unset($this->cn);
	}
}
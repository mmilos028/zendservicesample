<?php
/* Helper class to data conversion from Oracle database ref cursor into php arrays */
class CursorToArrayHelper {
	private $cursorData;
	private $firstRow;
	private $arrayData;
	public function __construct($_cursorData){
		$this->cursorData = $_cursorData;
		$this->cursorToIteratorOperation();
	}
	//converts cursor from database to array of php elements
	public static function cursorToArray($cursor){
		$arrData = array();
		foreach($cursor as $row)$arrData[] = $row;
		return $arrData;
	}
	//fills in firstRow data about pagination
	public function cursorToIteratorOperation(){
		//load arrayData with data from table except first row which is pagination information
		$i=0;
		$this->firstRow = array();
		$this->arrayData = array();
		foreach($this->cursorData as $row){
			if($i == 0)$this->firstRow[] = $row;
			else $this->arrayData[] = $row;
			$i++;
		}
	}
	//return first row data in table
	public function getPageRow(){ return $this->firstRow; }
	//return table data
	public function getTableRows(){return $this->arrayData; }
	
	static function getExceptionTraceAsString($exception) {
		$rtn = "";
		$count = 0;
		foreach ($exception->getTrace() as $frame) {
			$args = "";
			if (isset($frame['args'])) {
				$args = array();
				foreach ($frame['args'] as $arg) {
					if (is_string($arg)) {
						$args[] = "'" . $arg . "'";
					} elseif (is_array($arg)) {
						$args[] = "Array";
					} elseif (is_null($arg)) {
						$args[] = 'NULL';
					} elseif (is_bool($arg)) {
						$args[] = ($arg) ? "true" : "false";
					} elseif (is_object($arg)) {
						$args[] = get_class($arg);
					} elseif (is_resource($arg)) {
						$args[] = get_resource_type($arg);
					} else {
						$args[] = $arg;
					}
				}
				$args = join(", ", $args);
			}
			$rtn .= sprintf("<br /> #%s %s(%s): %s(%s)\n",
			$count,
			$frame['file'],
			$frame['line'],
			$frame['function'],
			$args );
			$count++;
		}
		$rtn = $exception->getMessage() . "<br /><br /><br />" .  $rtn;
		return $rtn;
	}
}
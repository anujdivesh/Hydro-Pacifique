<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------

 * @author  : Jim Hollington
 * @date    : 2010-03-24
 * @desc    : A simple class that takes 2 arrays and outputs a CSV file
 * @version : v0.1
 * @license : GPL

 */

class CSV {
 
	/**
	 * @param array: optional - Column headings
	 * @param array: optional - Content to go in CSV
	 * @param string: optional - File name to save CSV as
	 *
	 * @return object: CSV object
	 **/
	public function __construct($columns=null,$data=null,$file_name=null)
	{
		// if all required data has been passed to constructor then make csv immediately
		if( $columns && $data && $file_name )
		{
			$csv = $this->build($columns,$data);
			$this->save($csv,$file_name);
		}
	}
 
	/**
	 * @param array: Column headings
	 * @param array: Content to go in CSV
	 *
	 * @return string: CSV format string from input
	 **/
	public function build($columns, $data)
	{
		$csv = ''; // initialise csv variable
 
		foreach($columns as $heading) // csv column headings
		{
			$csv .= $heading.','; // concat heading onto row
		}
		$csv .= "\n"; // all the headings have been added so move to new line for csv content
 
		foreach($data as $row) // csv table content
		{
			foreach($columns as $column => $t)
			{
				if(strpos($row[$column],',')) // if cell content has a comma in it...
				{
					// ...double any existing quotes to escape them...
					$row[$column] = str_replace('"','""',$row[$column]);
					// ...and wrap the cell in quotes so the comma doesn't break everything.
					$row[$column] = '"'.$row[$column].'"';
				}
				$csv .= $row[$column].","; // concat the value onto the row
				if($t==end($columns))
				{
					// if we're at the end of a row move to a new line for next row
					$csv .= "\n"; 
				}
			}
		}
		return $csv;
	}
 
	/**
	 * @param string: String in CSV format
	 * @param string: optional - File name to save CSV as
	 *
	 * @return void
	 **/
	public function save($csv,$file_name=null)
	{
		// if no file name is provided set the file name to todays date
		if(!is_null($file_name)) $file_name = date('Y-m-d');
		// set content type and file name then output csv content
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$file_name.csv\"");
		echo $csv;
	}
}
 
?>

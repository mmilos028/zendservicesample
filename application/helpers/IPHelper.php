<?php
class IPHelper{
	//get real ip address from client accessing to web service where called
	public static function getRealIPAddress(){
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip);
		$ip = $ip_addresses[0];
		return $ip;
	}
	
	//test if ip address is in private range and not a public ip address
	//return true if in private range
	//return false if in public range (not a private ip address)
	public static function testPrivateIP($ip_address){
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];		
		$ip_start = ip2long("192.168.0.0");
		$ip_end = ip2long("192.168.255.255");
		$ip_test = ip2long($ip_address);
		if($ip_test > $ip_start && $ip_test < $ip_end){
			//if it is in private ip address range
			return true;
		}
		$ip_start = ip2long("10.0.0.0");
		$ip_end = ip2long("10.255.255.255");
		$ip_test = ip2long($ip_address);
		if($ip_test > $ip_start && $ip_test < $ip_end){
			//if it is in private ip address range
			return true;
		}
		$ip_start = ip2long("172.16.0.0");
		$ip_end = ip2long("172.31.255.255");
		$ip_test = ip2long($ip_address);
		if($ip_test > $ip_start && $ip_test < $ip_end){
			//if it is in private ip address range
			return true;
		}
		//if it is not in private ip address range
		return false;
	}
}
<?php
defined('IN_APP') or die;

interface NodeContent {
	public function html();
	public function getNodeID();
	public function setNodeID($id);
	public function insert();
	public function saveChanges();
}
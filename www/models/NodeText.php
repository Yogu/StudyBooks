<?php
defined('IN_APP') or die;

class NodeText extends Model implements NodeContent {
	public $nodeID;
	public $text = '';
	
	private static $parser;
	
	public static function table() {
		static $table;
		if (!isset($table)) {
			$table = new Table("NodeText", "NodesText", array(
				'nodeID',
				'text'));
		}
		return $table;
	}
	
	public function __construct($data = null) {
		parent::__construct($data);
	}
	
	public static function forNode(Node $node) {
		$t = self::query()
			->whereEquals('nodeID', $node->id)
			->first();
		if (!$t) {
			$t = new self();
			$t->nodeID = $node->id;
			$t->insert();
		}
		return $t;
	}
	
	public function insert() {
		$this->query()
			->insert(array(
				'nodeID' => $this->nodeID,
				'text' => $this->text));
	}
	
	public function saveChanges() {
		$this->query()
			->whereEquals('nodeID', $this->nodeID)
			->udpate(array('text' => $this->text));
	}
	
	public function getNodeID() {
		return $nodeID;
	}
	
	public function setNodeID($id) {
		$this->nodeID = $id;
	}
	
	public function html() {
		$parser = self::getParser();
		return $parser->transform($this->text);
		
		/*return '<p>'.str_replace("\n", "<br />", str_replace("\n\n",
				"</p><p>", $this->text)).'</p>';*/
	}
	
	private static function getParser() {
		if (!isset(self::$parser)) {
			Lib::loadMarkdown();
			self::$parser = new MarkdownExtra_Parser();
		}
		return self::$parser;
	}
}



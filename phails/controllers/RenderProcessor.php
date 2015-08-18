<?php
class RenderProcessor{

	public function render($template , $params){
		if(!empty($params)){
			foreach($params as $k => $v){
				${$k} = $v;
			}
		};
		include($template);
	}

	public function render_partial($template , $params = null){
		$template = Environment::$conf["viewDir"] . $template;
		echo $this->render($template , $params);
	}
}
?>

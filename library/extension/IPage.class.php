<?php defined('IN_FATE') or exit('access denied');

	/**
		* @brief 分页类
		* @param page       当前页
		* @param firstPage  第一页
		* @param lastPage   最后一页
		* @param pageSize   每页显示的条数
		* @param pageLength 显示的页码个数
		* @param total      总共条数
		* @param url        查询URL
		* @param type       分页样式
		*/
	
	class IPage {
		
		  public $page;
		  public $firstPage;
		  public $lastPage;
		  public $pageSize;
		  public $pageLength;
		  public $total;
		  public $url;
		  public $type;
		  static private $pageHtml;
		  
		  static public function run($total=1,$pageLength=10,$pageSize=1,$page=1,$url='',$type=1){
					 new IPage($total,$pageLength,$pageSize,$page,$url,$type);
					 return self::$pageHtml; 	 			
		 	}
		  
		  public function __construct($total=1,$pageLength=10,$pageSize=1,$page=1,$url='',$type=1){
		  	
		  		$this->total = intval($total);
		  		$this->page = (intval($page)>0)? $page:1;
		  		$this->pageSize = intval($pageSize);
		  		$this->pageLength = intval($pageLength);
		  		empty($url)? $this->setUrl($type):$url;
		  		$this->setLastPage();
		  		$this->setPageHtml();
		  }
		  		 	
		 	public function setLastPage(){
		 		  
		 			 $this->lastPage = floor(($this->total-1)/$this->pageSize)+1;
		 	}
		 	
		 	public function setUrl($type){
		 		
            $url = IUrl::getUri();
            $url = preg_replace('/(\?|&|\/)page(\/|=).*/i','',$url);
            if($type==1){
	            $index = (strpos($url,'?')!== false)?'&page=':'?page=';
	            $this->url = $url.$index;
          	}else{
          		$this->url =	rtrim($url,'/').'/page/';
          	}    
            
		 	}
		  
		  public function setPageHtml(){
		  			$pageHtml="<p class='page'>";
						if ($this->lastPage >= 1)
						{
							  $forMin = 1;
							  $forMax = 1;
							  
							  if(($this->page-1)%$this->pageLength==0 && $this->page==1){
							  		$forMin = $this->page;	
							  }else if($this->page%$this->pageLength==0){
							  		$forMin = $this->page-($this->pageLength-1);
							  }else{
							  		$bs = intval($this->page/$this->pageLength);
							  		$forMin = ($this->page>$this->pageLength)?($this->pageLength*$bs)+1:1;
							  }
							  
							  $forMax = $forMin+$this->pageLength-1;
							  $forMax = ($forMax>$this->lastPage)?$this->lastPage:$forMax;
							  
							  if($forMin>$this->pageLength){
							  	$pageHtml.=" <a href='".$this->url.($forMin-$this->pageLength)."' >上".$this->pageLength."页</a>";
								}
								
								$pageHtml.=" <a href='".$this->url."1'>首页</a>";
								for ($i=$forMin;$i<=$forMax;$i++){
									$clas = ($i==$this->page)?'class=\'selected\'':'';
									$pageHtml.=" <a href='".$this->url.$i."'".$clas.">".$i."</a>";
								}
								
								if(($forMax*$this->pageSize)<$this->total){
									$ding = $forMax+$this->pageLength;
									if($ding>$this->lastPage){
										$ding = 	$this->lastPage;
									}
									$pageHtml.=" <a href='".$this->url.($forMax+1)."'>....".$ding."</a>";
									$pageHtml.=" <a href='".$this->url.($forMax+1)."'>下".$this->pageLength."页</a>";
								}
								$pageHtml.=" <a href='".$this->url.$this->lastPage."'>末页</a>";
							//$pageHtml+=" <span>跳至</span><input type='text' style='width:25px;' />页<button id='go'>Go</button>";
								
						 }
						 $pageHtml.="</p>";
						 self::$pageHtml = $pageHtml;
		  }
	}

?>
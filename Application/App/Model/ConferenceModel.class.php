<?php
/**
*	会议
*/
namespace App\Model;
use Think\Model\RelationModel;
use Org\Nx\Response;
use Common\Third\AppPage;

class ConferenceModel extends RelationModel{
	 protected $_link = array(
		'Conference' => array(
			'mapping_type'  => self::HAS_MANY,
			'class_name'    => 'Conference',
			'foreign_key'   => 'id',
			'mapping_name'  => 'certify',
			'mapping_order' => 'id desc',
			// 定义更多的关联属性
			
		),
		
	);
	
	//分页测试
	public function allconf(){
		//会议进行中的数据
		
		$where = array();
		$showrow = 1; //一页显示的行数
		
		$curpage = I('post.page',1);; //当前的页,还应该处理非数字的情况
	
		
		$total = $this->alias('a')->where($where)->count();	
		
		
		$page = new AppPage($total, $showrow);
		if ($total > $showrow) {
			$data['page'] =  $page->myde_write();
		 }
		$data['data'] = $this->alias('a')
		->field('a.*,b.is_cert')
		->join('LEFT JOIN __CERTIFY__ b ON a.uid=b.uid
			')
		->where($where)
		->limit(($curpage - 1) * $showrow.','.$showrow)
		->order('a.id desc')
		->select();
		
		return $data;
	}
	
	/***
	* 首页获取所有数据
	*
	*/
	public function hunt(){
		//会议进行中的数据
		$where['statuses'] = 0 ;
		$showrow = 15; //一页显示的行数
		
		$curpage = I('post.page',1);; //当前的页,还应该处理非数字的情况
	
		
		$total = $this->alias('a')->where($where)->count();	
		
		
		$page = new AppPage($total, $showrow);
		if ($total > $showrow) {
			$data['page'] =  $page->myde_write();
		 }
		$data['data'] = $this->alias('a')
		->field('a.*,b.is_cert')
		->join('LEFT JOIN __CERTIFY__ b ON a.uid=b.uid
			')
		->where($where)
		->limit(($curpage - 1) * $showrow.','.$showrow)
		->order('a.id desc')
		->select();
		return $data;
	}
	
	/**
	 * 搜索数据
	 */
	public function searchFront(){
		
		//搜索
		$where = array();
		//$where['a.uid'] = I('post.uid');
		$where['statuses'] = 0;
		$where['is_private'] = 0;
		$title = I('post.title');
		if ( $title ) {
			$where['title'] = array('like',"%$title%");
		}else{
			return false;
		}
		
		$uid = I('post.uid');
		
		if($uid){
			//
			$where['uid'] =  array('neq',$uid);
		}
		$showrow = 15; //一页显示的行数
		
		$curpage = I('post.page',1);; //当前的页,还应该处理非数字的情况


		$total = $this->alias('a')->where($where)->count();	


		$page = new AppPage($total, $showrow);
		if ($total > $showrow) {
			$data['page'] =  $page->myde_write();
		 }
		
		$data['data'] = $this
		->where($where)
		->limit(($curpage - 1) * $showrow.','.$showrow)
		->order('id desc')
		->select();
		//p($this->_Sql());
		return $data;
	}
	//发布的会议
	public function sendConf(){
		
		//搜索
		$where = array();
		$where['uid'] = I('post.user_id');
		$where['statuses'] = I('post.state');
		$where['is_private'] = I('post.is_private');
		$showrow = 15; //一页显示的行数
		
		$curpage = I('post.page',1);; //当前的页,还应该处理非数字的情况


		$total = $this->alias('a')->where($where)->count();	


		$page = new AppPage($total, $showrow);
		if ($total > $showrow) {
			$data['page'] =  $page->myde_write();
		 }
		$data['data'] = $this->alias('a')
		->field('a.*,c.catename,d.pic,d.bullurl')
		->join('LEFT JOIN __CONFERENCE_CATE__ c on c.id=a.cid
			LEFT JOIN __CONFERENCE_PIC__ d on d.conf_id=a.id
			')
		->where($where)
		->limit(($curpage - 1) * $showrow.','.$showrow)
		->order('a.id desc')
		->group('a.id')
		->select();
		//p($this->_Sql());die;
		return $data;
	}
	
	
	/**
	 * 前台单条数据
	 */
	public function showOne(){
		
		//搜索
		$where = array();
		//$where['a.uid'] = I('post.uid');
		
		$where['id'] = I('post.id');
		
		
		$data['data'] = $this
		->where($where)
		->order('id desc')
		->find();
		//p($this->getLastSql());
		return $data;
	}
	//会议详情滚动图片
	public function qupic(){
		$pic = D('Conference_pic');
		$where = array();
		$where['conf_id'] = I('post.id');
		$data['data'] = $pic
		->where($where)
		->order('id desc')
		->limit('0,3')
		->select();
		return $data;
	}
	
	/**
	 * 后台数据
	 */
	public function search($pagesize=15){

		//搜索
		$where = array();
		
		$title = I('get.title');
		if ($title) {
			$where['a.title'] = array('like',"%$title%");
		}
		//翻页
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count,$pagesize);
		//配置分页
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		$data['data'] = $this->alias('a')
		->field('a.*,b.username,c.catename')
		->join('LEFT JOIN __USER__ b ON a.uid=b.id
			LEFT JOIN __CONFERENCE_CATE__ c on c.id=a.cid
		')
		->where($where)
		->limit($page->firstRow.','.$page->listRows)
		->order('id desc')
		->select();
		
		return $data;
	}

	//插入前操作
	public function _before_insert(&$data, $option){
		
		$data['uid'] = I('post.uid');
		$data['addtime'] = time();
		//app 上传图片
		$data['companypic'] = json_encode(app_upload_image("/Uploads/Conference"));
	}
	
	
	//修改之前
	public function _before_update(&$data, $option){
		//app 修改上传图片
		$data['companypic'] = json_encode(app_upload_image("/Uploads/Conference"));
		//p($data);
	}
	
	//筛选条件查询
	public function filterCert(){
		$where = array();
		$catename = I('post.catename');
		if($catename != 1){
			$where['cid'] =$catename;
		}
		$uid = I('post.uid');
		
		if($uid){
			//
			$where['uid'] =  array('neq',$uid);
		}
		$addId = I('post.addId');
		$t = time();
		$three = $t-3600*24*3; //三天
		$mouth = $t-3600*24*30; //一个月
		$mouth3 = $t-3600*24*30*3; //三个月
		switch ( $addId ) {
			case 1:
			$where['addtime'] =  array(array('elt' , $t ),array('egt' , $three ));
			break;  
			case 2:
			$where['addtime'] =  array(array('elt' , $t ),array('egt' , $mouth ));
			break;
			case 3:
			$where['addtime'] =  array(array('elt' , $t ),array('egt' , $mouth3 ));
			break;
			default:
			
			}
		
		
		$address = I('post.address');
		if($address ){
			$where['address'] =  array('like',"%$address%");
		}
		
		//状态为进行中
		$where['statuses'] = 0;
		//公开的会议
		$where['is_private'] = 0;
		
		$showrow = 10; //一页显示的行数
		
		$curpage = I('post.page',1);; //当前的页,还应该处理非数字的情况
	
		$total = $this->where($where)->count();	
		
		
		$page = new AppPage($total, $showrow);
		if ($total > $showrow) {
			$data['page'] =  $page->myde_write();
		 }
		
		
		$data['data'] = $this/* ->alias('a')
		->field('a.*,b.is_cert,c.catename')
		->join('LEFT JOIN __CERTIFY__ b ON a.uid=b.uid 
				LEFT JOIN __CONFERENCE_CATE__ c ON c.id=a.cid 
			') */
		->limit(($curpage - 1) * $showrow.','.$showrow)
		->where($where)
		->order('id desc')
		->select();
		
		//p($this->getLastSql());die;
	
		return $data;
		
	}
	
	
	//二级账户显示主账户的内部会议列表
	public function privateConfList(){
		$data['uid'] = I('post.uid');
		$user = D('User');
		$map = $user->field('id,pid')->where(array('id'=>$data['uid']))->find();
		//p($map);
		//内部私密会议
		$where['is_private'] = 1;
		//会议状态 0开始1结束
		$where['statuses'] = I('post.state');
		$where['uid'] = $map['pid'];
		
		$showrow = 10; //一页显示的行数
		
		$curpage = I('post.page',1);; //当前的页,还应该处理非数字的情况
	
		$total = $this->where($where)->count();	
		
		
		$page = new AppPage($total, $showrow);
		if ($total > $showrow) {
			$data['page'] =  $page->myde_write();
		}
		 
		$data['data'] = $this->alias('a')
		->field('a.*,c.catename,d.pic,d.bullurl')
		->join('LEFT JOIN __CONFERENCE_CATE__ c on c.id=a.cid
			LEFT JOIN __CONFERENCE_PIC__ d on d.conf_id=a.id
			')
		->where($where)
		->limit(($curpage - 1) * $showrow.','.$showrow)
		->order('a.id desc')
		->group('a.id')
		->select();
		//p($this->_Sql());die;
		return $data;
		
		
	}
	
	
	

}
?>
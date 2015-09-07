<?php
/**
 * 我的工作，服务接口
 * panjie 
 * 3792535@qq.com
 * */
 namespace Myjob\Service;
 use Examine\Model\ExamineModel;	//审核流程表
 use Chain\Model\ChainModel;		//审核流程链表
 use Workflow\Model\WorkflowModel;	//工作流表
 use WorkflowLog\Model\WorkflowLogModel;	//工作流结点表
 use UserDepartmentPost\Model\UserDepartmentPostModel;	//用户部门岗位表
 use Chain\Logic\ChainLogic;

 class MyjobService
 {
 	protected $error;

 	public function __set($name , $value) {
 		$this->$name = $value;
        return ;
    }
 	 /**
 	  * 
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }

    /**
     * 添加新的工作流
     * @param num $userId                申请人\拟搞人
     * @param number $examineId             审核流程ID
     * @param num $publicProjetcDetailId 项目ID
     * @param num $checkUserId           用户选择的工作流审核人员
     */
    public function add($userId , $examineId , $publicProjetcDetailId, $checkUserId)
    {
    	//取审核流程信息
    	$ExamineM = new ExamineModel();
    	$map = array();
    	$map['id'] = $examineId;
    	$examine = $ExamineM->where($map)->find();
    	if(!$examine)
    	{
    		$this->error = "传入examineid有误，不存在该审核流程记录";
    		return false;
    	}

    	//取开始岗位信息
    	$chainId = $examine['chain_id'];
    	$ChainM = new ChainModel();
    	$map = array();
    	$map['id'] = $chainId;
    	$chain = $ChainM->where($map)->find();

    	//判断申请人是否具有该岗位
    	$postId = $chain['now_post'];
    	$UserDepartmentPostM = new UserDepartmentPostModel();
    	$userDepartmentPost = $UserDepartmentPostM->getListByUserIdPostId($userId,$postId);
    	if($userDepartmentPost == null)
    	{
    		$this->error = "该用户选择的审核流程不在其可选范围内";
    		return false;
    	}

    	//取当前用户当前结点的所有审核用户列表
    	$ChainL = new ChainLogic();
    	$userLists = $ChainM->getNextExaminUsersByUserIdAndId($userId , $chainId);


    	//判断传入 审核用户 是否处于列表中
    	if(!isset($userLists[$checkUserId]))
    	{
    		$this->error = "提交审核人员信息有误，该审核人员不属于该用户对应的该流程";
    		return false;
    	}

    	//添加工作流数据
    	$data = array();
    	$data['examine_id'] = $examineId;
    	$data['chain_id']	= $chainId;
    	$data['public_project_detail_id'] 	= $publicProjetcDetailId;
    	$WorkflowM = new WorkflowModel();
    	if(!$WorkflowM->create($data))
    	{
    		$this->error = $WorkflowM->getError();
    		return false;
    	}
    	$workflowId = $WorkflowM->add();

    	//存工作流结点表
    	$data 	= array();
    	$data['workflow_id']	= $workflowId;
    	$data['pre_id'] = '0';
    	$data['user_id'] = $checkUserId;
    	$WorkflowLogM = new WorkflowLogM();
    	if(!$WorkflowLogM->create($data))
    	{
    		$this->error = $WorkflowLogM->getError();
    		$this->error .= "工作流表数据已经写进去了……，郁闷的，就这样吧。"
    		return false;
    	}
    	$WorkflowLogm->add();

    	return;
    }
 }
<?php

namespace App\Http\Controllers\Cloud;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Cloud\ResourcesShare;

/**
 * 资源分享
 * @date : 2022/4/21 19:26
 * @author : 孤鸿渺影
 */
class ResourcesShareController  extends Controller
{

	/**
	* 列表
	* @Author:System Generate
	* @Date:2022-04-21 19:25:18
	* @return string
	*/
	public function index(): string
	{
		return resourceConstructor(new ResourcesShare())->index();
	}

	/**
	* 创建
	* @Author:System Generate
	* @Date:2022-04-21 19:25:18
	* @param  Request  $request
	* @return string
	*/
	public function store(Request $request): string
	{
		return resourceConstructor(new ResourcesShare())->store($request);
	}

	/**
	* 详情(多条件)
	* @Author:System Generate
	* @Date:2022-04-21 19:25:18
	* @param  Request  $request
	* @return string
	*/
	public function get(Request $request): string
	{
		return resourceConstructor(new ResourcesShare())->get($request);
	}

	/**
	* 详情
	* @Author:System Generate
	* @Date:2022-04-21 19:25:18
	* @param  Request  $request
	* @return string
	*/
	public function show($id): string
	{
		return resourceConstructor(new ResourcesShare())->show($id);
	}

	/**
	* 修改
	* @Author:System Generate
	* @Date:2022-04-21 19:25:18
	* @param  Request  $request
	* @return string
	*/
	public function update(Request $request, $id): string
	{
		return resourceConstructor(new ResourcesShare())->update($request,$id);
	}

	/**
	* 删除
	* @Author:System Generate
	* @Date:2022-04-21 19:25:18
	* @return string
	*/
	public function destroy($id): string
	{
		return resourceConstructor(new ResourcesShare())->destroy($id);
	}
}

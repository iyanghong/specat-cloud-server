<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Member\Role;

class RoleController  extends Controller
{

	/**
	* 列表
	* @Author:System Generate
	* @Date:2022-04-17 21:35:36
	* @return string
	*/
	public function index(): string
	{
		return resourceConstructor(new Role())->index();
	}

	/**
	* 创建
	* @Author:System Generate
	* @Date:2022-04-17 21:35:36
	* @param  Request  $request
	* @return string
	*/
	public function store(Request $request): string
	{
		return resourceConstructor(new Role())->store($request);
	}

	/**
	* 详情(多条件)
	* @Author:System Generate
	* @Date:2022-04-17 21:35:36
	* @param  Request  $request
	* @return string
	*/
	public function get(Request $request): string
	{
		return resourceConstructor(new Role())->get($request);
	}

    /**
     * 详情
     * @Author:System Generate
     * @Date:2022-04-17 21:35:36
     * @param $id
     * @return string
     */
	public function show($id): string
	{
		return resourceConstructor(new Role())->show($id);
	}

    /**
     * 修改
     * @Author:System Generate
     * @Date:2022-04-17 21:35:36
     * @param Request $request
     * @param $id
     * @return string
     */
	public function update(Request $request, $id): string
	{
		return resourceConstructor(new Role())->update($request,$id);
	}

    /**
     * 删除
     * @Author:System Generate
     * @Date:2022-04-17 21:35:36
     * @param $id
     * @return string
     */
	public function destroy($id): string
	{
		return resourceConstructor(new Role())->destroy($id);
	}
}

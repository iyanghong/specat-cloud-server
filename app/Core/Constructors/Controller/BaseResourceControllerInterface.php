<?php


namespace App\Core\Constructors\Controller;

use App\Exceptions\NoLoginException;
use Illuminate\Http\Request;
use Throwable;

interface BaseResourceControllerInterface
{
    /**
     * Display a listing of the resource.
     *
     * @return string
     */
    public function index(): string;

    /**
     * Show the form for creating a new resource.
     *
     * @return string
     */
    public function create(): string;

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string;

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return string
     */
    public function show($id): string;

    /**
     *
     * @param Request $request
     * @param array $option
     * Date : 2021/4/20 21:27
     * Author : 孤鸿渺影
     * @return string
     */
    public function get(Request $request, array $option = []): string;

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return string
     */
    public function edit($id): string;

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function update(Request $request, $id): string;

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return string
     */
    public function destroy($id): string;

    /**
     * 批量插入
     * Date : 2021/4/20 22:51
     * Author : 孤鸿渺影
     * @return string
     * @throws Throwable
     */
    public function batchInsert(): string;

    /**
     *
     * @param array $field
     * @param array $option
     * Date : 2021/4/21 22:10
     * Author : 孤鸿渺影
     * @return string
     * @throws NoLoginException
     */
    public function listNowOnlineUser(array $field = ['uuid' => 'user_uuid'], array $option = []): string;
}

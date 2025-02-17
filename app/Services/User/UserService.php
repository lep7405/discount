<?php

namespace App\Services\User;

interface UserService
{
    public function create(array $attributes);

    public function login(array $attributes);

    public function store($request);

    public function show($id);

    public function edit($id);

    public function update($request, $id);

    public function destroy($id);
}

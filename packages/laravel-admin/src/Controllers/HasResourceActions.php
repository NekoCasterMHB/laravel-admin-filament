<?php

namespace Encore\Admin\Controllers;

trait HasResourceActions
{
    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param array $data
     *
     * @return mixed
     */
    public function update($id, array $data = null)
    {
        return $this->form()->update($data ?? request()->all(), $id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data = null)
    {
        return $this->form()->store($data ?? request()->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return bool
     */
    public function destroy($id)
    {
        return $this->form()->destroy($id);
    }
}

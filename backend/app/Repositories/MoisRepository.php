<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Mois;

final class MoisRepository
{
    private Mois $mois;

    /**
     * Mois constructor.
     */
    public function __construct(Mois $mois)
    {
        $this->mois = $mois;
    }

    /**
     * Get all mois.
     *
     * @return Mois $mois
     */
    public function all()
    {
        return $this->mois->get();
    }

    /**
     * Get mois by id
     *
     * @return mixed
     */
    public function getById(int $id)
    {
        return $this->mois->find($id);
    }

    /**
     * Save Mois
     *
     * @return Mois
     */
    public function save(array $data)
    {
        return Mois::create($data);
    }

    /**
     * Update Mois
     *
     * @return Mois
     */
    public function update(array $data, int $id)
    {
        $mois = $this->mois->find($id);
        $mois->update($data);

        return $mois;
    }

    /**
     * Delete Mois
     *
     * @param  $data
     * @return Mois
     */
    public function delete(int $id)
    {
        $mois = $this->mois->find($id);
        $mois->delete();

        return $mois;
    }
}

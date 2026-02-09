<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MoisRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class MoisService
{
    /**
     * @var MoisRepository
     */
    private $moisRepository;

    /**
     * DummyClass constructor.
     */
    public function __construct(MoisRepository $moisRepository)
    {
        $this->moisRepository = $moisRepository;
    }

    /**
     * Get all moisRepository.
     *
     * @return string
     */
    public function getAll()
    {
        return $this->moisRepository->all();
    }

    /**
     * Get moisRepository by id.
     *
     * @return string
     */
    public function getById(int $id)
    {
        return $this->moisRepository->getById($id);
    }

    /**
     * Validate moisRepository data.
     * Store to DB if there are no errors.
     *
     * @return string
     */
    public function save(array $data)
    {
        return $this->moisRepository->save($data);
    }

    /**
     * Update moisRepository data
     * Store to DB if there are no errors.
     *
     * @return string
     */
    public function update(array $data, int $id)
    {
        DB::beginTransaction();
        try {
            $moisRepository = $this->moisRepository->update($data, $id);
            DB::commit();

            return $moisRepository;
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            throw new InvalidArgumentException('Unable to update post data');
        }
    }

    /**
     * Delete moisRepository by id.
     *
     * @return string
     */
    public function deleteById(int $id)
    {
        DB::beginTransaction();
        try {
            $moisRepository = $this->moisRepository->delete($id);
            DB::commit();

            return $moisRepository;
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            throw new InvalidArgumentException('Unable to delete post data');
        }
    }
}

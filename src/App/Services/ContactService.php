<?php

namespace App\Services;

use App\Models\Contact;
use App\Repositories\ContactRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис, для работы с контактами в БД
 */
class ContactService
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    public function __construct()
    {
        $this->contactRepository = new ContactRepository();
    }

    /**
     * Создает или обновляет запись в таблице contacts
     * @param array $data
     * @return Contact - созданный или обновленный контакт
     */
    public function createOrUpdate(array $data): Contact
    {
        return $this->contactRepository->createOrUpdate($data);
    }

    /**
     * Создает или обновляет записи в таблице contacts
     * @param array $contacts
     */
    public function createOrUpdateMany(array $contacts)
    {
        foreach ($contacts as $contact) {
            $this->createOrUpdate([
                'id' => $contact['id'],
                'email' => $contact['email'],
            ]);
        }
    }
    /**
     * Удаляет записи из таблицы contacts
     * @param array $ids - массив с id контактов
     * @return void
     */
    public function deleteMany(array $ids)
    {
        foreach ($ids as $id) {
            $this->contactRepository->delete($id);
        }
    }

    /**
     * Возвращает массив с email-ами контактов
     * @param array $ids - массив с id контактов
     * @return array - массив с email-ами контактов вида [id => [email1, email2]]
     */
    public function getEmails(array $ids): array
    {
        return $this->contactRepository->getEmails($ids);
    }

    /**
     * Удаляет email из записи контакта
     * @param array $data - массив в формате [id => [email1, email2]]
     * @return void
     */
    public function deleteEmails(array $data): void
    {
        foreach ($data as $id => $emails) {
            foreach ($emails as $email) {
                $this->contactRepository->deleteEmail($email, (int) $id);
            }
        }
    }
}

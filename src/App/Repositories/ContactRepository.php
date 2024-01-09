<?php

namespace App\Repositories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;

/**
 * Репозиторий для работы с таблицей accounts
 */
class ContactRepository
{
    /**
     * @var Contact
     */
    private $contact;

    public function __construct()
    {
        $this->contact = new Contact();
    }
    /**
     * Возвращает записи по id контакта
     * @param int $id
     * @return Collection
     */
    public function findById(int $id): Collection
    {
        return $this->contact->where('id', $id)->get();
    }
    /**
     * Создает или обновляет запись в таблице contacts
     */
    public function createOrUpdate(array $data): Contact
    {
        $this->contact->updateOrCreate($data);
        return $this->contact;
    }
    /**
     * Удаляет запись из таблицы contacts
     */
    public function delete(int $id): void
    {
        $this->contact->where('id', $id)->delete();
    }

    /**
     * Возвращает массив с email-ами контактов
     * @param array $ids - массив с id контактов
     * @return array - массив с email-ами контактов вида [id => [email1, email2]]
     */
    public function getEmails(array $ids): array
    {
        $emails = [];
        foreach ($ids as $id) {
            foreach ($this->findById($id) as $contact) {
                $emails[$id][] = $contact->email;
            }
        }
        return $emails;
    }

    /**
     * Удаляет email из записи контакта
     */
    public function deleteEmail(string $email, int $id): void
    {
        $this->contact->where('email', $email)->where('id', $id)->delete();
    }
}

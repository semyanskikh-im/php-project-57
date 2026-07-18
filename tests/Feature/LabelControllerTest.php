<?php

namespace Tests\Feature;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    /**
     * Тест: неавторизованный пользователь может видеть список меток
     */
    public function test_guest_can_view_labels()
    {
        Label::factory()->create(['name' => 'ошибка']);
        Label::factory()->create(['name' => 'фича']);

        $response = $this->get(route('labels.index'));
        $response->assertStatus(200);
        $response->assertViewIs('labels.index');
        $response->assertSee('ошибка');
        $response->assertSee('фича');
    }

    /**
     * Тест: неавторизованный пользователь НЕ может видеть форму создания
     */
    public function test_guest_cannot_view_create_form()
    {
        $response = $this->get(route('labels.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Тест: неавторизованный пользователь НЕ может создать метку
     */
    public function test_guest_cannot_store_label()
    {
        $labelData = Label::factory()
            ->make()
            ->toArray();

        $response = $this->post(route('labels.store'), $labelData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('labels', ['name' => $labelData['name']]);
    }

    /**
     * Тест: неавторизованный пользователь НЕ может видеть форму редактирования
     */
    public function test_guest_cannot_view_edit_form()
    {
        $label = Label::factory()->create();

        $response = $this->get(route('labels.edit', $label));
        $response->assertRedirect(route('login'));
    }

    /**
     * Тест: неавторизованный пользователь НЕ может обновить метку
     */
    public function test_guest_cannot_update_label()
    {
        $label = Label::factory()->create(['name' => 'Старое имя']);

        $updatedData = Label::factory()
            ->make(['name' => 'Новое имя'])
            ->toArray();

        $response = $this->put(route('labels.update', $label), $updatedData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('labels', ['name' => 'Старое имя']);
        $this->assertDatabaseMissing('labels', ['name' => $updatedData['name']]);
    }

    /**
     * Тест: неавторизованный пользователь НЕ может удалить метку
     */
    public function test_guest_cannot_delete_label()
    {
        $label = Label::factory()->create();

        $response = $this->delete(route('labels.destroy', $label));
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('labels', ['id' => $label->id]);
    }

    /**
     * Тест: авторизованный пользователь может видеть список меток
     */
    public function test_authenticated_user_can_view_labels()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('labels.index'));
        $response->assertStatus(200);
        $response->assertViewIs('labels.index');
    }

    /**
     * Тест: авторизованный пользователь может видеть форму создания
     */
    public function test_authenticated_user_can_view_create_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('labels.create'));
        $response->assertStatus(200);
        $response->assertViewIs('labels.create');
    }

    /**
     * Тест: авторизованный пользователь может создать метку
     */
    public function test_authenticated_user_can_create_label()
    {
        $user = User::factory()->create();

        $labelData = Label::factory()
            ->make()
            ->toArray();

        $response = $this->actingAs($user)->post(route('labels.store'), $labelData);

        $response->assertRedirect(route('labels.index'));
        $this->assertDatabaseHas('labels', ['name' => $labelData['name']]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: метка требует название
     */
    public function test_label_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('labels.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Тест: имя метки должно быть уникальным
     */
    public function test_label_name_must_be_unique()
    {
        $user = User::factory()->create();

        Label::create(['name' => 'ошибка']);

        $response = $this->actingAs($user)->post(route('labels.store'), [
            'name' => 'ошибка',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Тест: авторизованный пользователь может видеть форму редактирования
     */
    public function test_authenticated_user_can_view_edit_form()
    {
        $user = User::factory()->create();
        $label = Label::factory()->create();

        $response = $this->actingAs($user)->get(route('labels.edit', $label));

        $response->assertStatus(200);
        $response->assertViewIs('labels.edit');
        $response->assertViewHas('label', $label);
    }

    /**
     * Тест: авторизованный пользователь может обновить метку
     */
    public function test_authenticated_user_can_update_label()
    {
        $user = User::factory()->create();
        $label = Label::factory()->create(['name' => 'Старое имя']);

        $updatedData = Label::factory()
            ->make(['name' => 'Новое имя'])
            ->toArray();

        $response = $this->actingAs($user)->put(route('labels.update', $label), $updatedData);

        $response->assertRedirect(route('labels.index'));
        $this->assertDatabaseHas('labels', ['name' => $updatedData['name']]);
        $this->assertDatabaseMissing('labels', ['name' => 'Старое имя']);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: авторизованный пользователь может удалить метку (без связанных задач)
     */
    public function test_authenticated_user_can_delete_label_without_tasks()
    {
        $user = User::factory()->create();
        $label = Label::factory()->create();

        $response = $this->actingAs($user)->delete(route('labels.destroy', $label));

        $response->assertRedirect(route('labels.index'));
        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: НЕЛЬЗЯ удалить метку, если она связана с задачей
     */
    public function test_cannot_delete_label_with_tasks()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $label = Label::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        // Привязываем метку к задаче (многие ко многим)
        $task->labels()->attach($label->id);

        $response = $this->actingAs($user)->delete(route('labels.destroy', $label));

        $response->assertRedirect(route('labels.index'));
        $response->assertSessionHas('flash_notification');
        $this->assertDatabaseHas('labels', ['id' => $label->id]);
    }
}

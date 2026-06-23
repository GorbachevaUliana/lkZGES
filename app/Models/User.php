<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;
    // role, status, permissions намеренно НЕ в fillable —
    // защита от mass-assignment privilege escalation.
    // Эти поля выставляются только явно (User::query()->update / ->forceFill).
    protected $fillable = [
        'name', 'email', 'password',
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
    ];

    public function canAccessPanel(Panel $panel): bool
    {   
        if ($panel->getId() === 'manager') {
            return $this->role === 'admin';
        }

        return false;
    }

    // ==================== RELATIONSHIPS ====================
    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function properties(): HasManyThrough
    {
        return $this->hasManyThrough(Property::class, Client::class);
    }

    /**
     * Обращения пользователя
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Заявки пользователя
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // ==================== ROLE CHECKS ====================

    /**
     * Проверка: гость (не подал заявку, не привязал ЛС)
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Проверка: заявитель (подал заявку, ждёт рассмотрения)
     */
    public function isApplicant(): bool
    {
        return $this->role === 'applicant';
    }

    /**
     * Проверка: полноценный клиент с ЛС
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Проверка: сотрудник
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Проверка: администратор
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Имеет доступ к админ-панели (staff или admin)
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['staff', 'admin']);
    }

    /**
     * Может создавать обращения (только полноценный клиент)
     * ИСПРАВЛЕНО: Проверяем также наличие активных объектов
     */
    public function canCreateTickets(): bool
    {
        // Проверяем роль client И наличие хотя бы одного активного объекта с ЛС
        if ($this->role !== 'client') {
            return false;
        }

        return $this->hasActiveProperties();
    }

    /**
     * Проверка наличия активных объектов с ЛС
     */
    public function hasActiveProperties(): bool
    {
        // Используем существующую связь properties (hasManyThrough)
        return $this->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->exists();
    }

    /**
     * Получить все активные объекты пользователя
     */
    public function getActiveProperties()
    {
        return $this->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->get();
    }

    /**
     * Имеет доступ к личному кабинету (applicant или client)
     */
    public function canAccessClientArea(): bool
    {
        return in_array($this->role, ['applicant', 'client']);
    }

    // ==================== PERMISSION CHECKS ====================

    /**
     * Проверка разрешения по ID страницы
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    // ==================== SCOPES ====================

    public function scopeGuests($query)
    {
        return $query->where('role', 'guest');
    }

    public function scopeApplicants($query)
    {
        return $query->where('role', 'applicant');
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }
}
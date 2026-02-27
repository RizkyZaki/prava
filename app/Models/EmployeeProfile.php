<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'national_id_number',
        'birth_date',
        'hire_date',
        'bjb_bank_account_number',
        'tax_identification_number',
        'position_title',
        'address',
        'phone_number',
        'personal_email',
        'marital_status',
        'last_education',
        'profile_photo',
        'id_card_attachment',
        'tax_attachment',
        'employment_contract_attachment',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
    ];

    // Accessors/mutators for DB column mapping if needed
    public function getNikAttribute() { return $this->attributes['nik'] ?? $this->attributes['national_id_number'] ?? null; }
    public function setNikAttribute($value) { $this->attributes['national_id_number'] = $value; $this->attributes['nik'] = $value; }

    public function getTanggalLahirAttribute() { return $this->attributes['tanggal_lahir'] ?? $this->attributes['birth_date'] ?? null; }
    public function setTanggalLahirAttribute($value) { $this->attributes['birth_date'] = $value; $this->attributes['tanggal_lahir'] = $value; }

    public function getTanggalMasukAttribute() { return $this->attributes['tanggal_masuk'] ?? $this->attributes['hire_date'] ?? null; }
    public function setTanggalMasukAttribute($value) { $this->attributes['hire_date'] = $value; $this->attributes['tanggal_masuk'] = $value; }

    public function getRekeningBjbAttribute() { return $this->attributes['rekening_bjb'] ?? $this->attributes['bjb_bank_account_number'] ?? null; }
    public function setRekeningBjbAttribute($value) { $this->attributes['bjb_bank_account_number'] = $value; $this->attributes['rekening_bjb'] = $value; }

    public function getNpwpAttribute() { return $this->attributes['npwp'] ?? $this->attributes['tax_identification_number'] ?? null; }
    public function setNpwpAttribute($value) { $this->attributes['tax_identification_number'] = $value; $this->attributes['npwp'] = $value; }

    public function getJabatanAttribute() { return $this->attributes['jabatan'] ?? $this->attributes['position_title'] ?? null; }
    public function setJabatanAttribute($value) { $this->attributes['position_title'] = $value; $this->attributes['jabatan'] = $value; }

    public function getAlamatAttribute() { return $this->attributes['alamat'] ?? $this->attributes['address'] ?? null; }
    public function setAlamatAttribute($value) { $this->attributes['address'] = $value; $this->attributes['alamat'] = $value; }

    public function getNoHpAttribute() { return $this->attributes['no_hp'] ?? $this->attributes['phone_number'] ?? null; }
    public function setNoHpAttribute($value) { $this->attributes['phone_number'] = $value; $this->attributes['no_hp'] = $value; }

    public function getEmailPribadiAttribute() { return $this->attributes['email_pribadi'] ?? $this->attributes['personal_email'] ?? null; }
    public function setEmailPribadiAttribute($value) { $this->attributes['personal_email'] = $value; $this->attributes['email_pribadi'] = $value; }

    public function getStatusPernikahanAttribute() { return $this->attributes['status_pernikahan'] ?? $this->attributes['marital_status'] ?? null; }
    public function setStatusPernikahanAttribute($value) { $this->attributes['marital_status'] = $value; $this->attributes['status_pernikahan'] = $value; }

    public function getPendidikanTerakhirAttribute() { return $this->attributes['pendidikan_terakhir'] ?? $this->attributes['last_education'] ?? null; }
    public function setPendidikanTerakhirAttribute($value) { $this->attributes['last_education'] = $value; $this->attributes['pendidikan_terakhir'] = $value; }

    public function getFotoProfilAttribute() { return $this->attributes['foto_profil'] ?? $this->attributes['profile_photo'] ?? null; }
    public function setFotoProfilAttribute($value) { $this->attributes['profile_photo'] = $value; $this->attributes['foto_profil'] = $value; }

    public function getAttachmentKtpAttribute() { return $this->attributes['attachment_ktp'] ?? $this->attributes['id_card_attachment'] ?? null; }
    public function setAttachmentKtpAttribute($value) { $this->attributes['id_card_attachment'] = $value; $this->attributes['attachment_ktp'] = $value; }

    public function getAttachmentNpwpAttribute() { return $this->attributes['attachment_npwp'] ?? $this->attributes['tax_attachment'] ?? null; }
    public function setAttachmentNpwpAttribute($value) { $this->attributes['tax_attachment'] = $value; $this->attributes['attachment_npwp'] = $value; }

    public function getAttachmentKontrakAttribute() { return $this->attributes['attachment_kontrak'] ?? $this->attributes['employment_contract_attachment'] ?? null; }
    public function setAttachmentKontrakAttribute($value) { $this->attributes['employment_contract_attachment'] = $value; /* legacy attr removed */ }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

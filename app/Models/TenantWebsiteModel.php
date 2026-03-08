<?php
namespace App\Models;

class TenantWebsiteModel extends BaseMultiTenantModel
{
    protected $table         = 'tenant_websites';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'theme_slug', 'primary_color', 'hero_title', 'hero_subtitle', 'about_text', 'policies_text', 'instagram_url', 'facebook_url', 'whatsapp_number', 'is_published'];
}
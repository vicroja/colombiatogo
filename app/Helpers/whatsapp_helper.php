<?php

/**
 * WhatsApp Helper
 * Contiene funciones de utilidad para parsear mensajes de plantillas de Meta.
 */

if (!function_exists('parse_whatsapp_template_message')) {

    function parse_whatsapp_template_message(string $baseTemplateText, ?string $templateDataJson, string $fallbackBody = '')
    {
        if (empty($baseTemplateText)) {
            return $fallbackBody ?: '(No se pudo cargar el texto base de la plantilla)';
        }

        if (empty($templateDataJson)) {
            return $baseTemplateText;
        }

        $templateData = json_decode($templateDataJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $fallbackBody ?: '(Error decodificando JSON de la plantilla)';
        }

        // Buscar componentes de la plantilla en el formato de Meta
        $components = [];
        if (isset($templateData['template']['components']) && is_array($templateData['template']['components'])) {
            $components = $templateData['template']['components'];
        } elseif (isset($templateData['components']) && is_array($templateData['components'])) {
            $components = $templateData['components'];
        } else {
            return $baseTemplateText;
        }

        $parametersTextValues = [];

        // Extraer los valores que reemplazarán a {{1}}, {{2}}...
        foreach ($components as $component) {
            if (isset($component['parameters']) && is_array($component['parameters'])) {
                foreach ($component['parameters'] as $parameter) {
                    if (isset($parameter['type']) && strtolower($parameter['type']) === 'text' && isset($parameter['text'])) {
                        $parametersTextValues[] = $parameter['text'];
                    }
                }
            }
        }

        if (empty($parametersTextValues)) {
            return $baseTemplateText;
        }

        // Reemplazo mágico de {{1}}, {{2}}, etc. usando Regex
        $parsedText = preg_replace_callback('/\{\{(\d+)\}\}/', function ($matches) use ($parametersTextValues) {
            $index = intval($matches[1]) - 1;
            return $parametersTextValues[$index] ?? $matches[0];
        }, $baseTemplateText);

        return $parsedText;
    }
}
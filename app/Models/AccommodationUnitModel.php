<?php
namespace App\Models;

class AccommodationUnitModel extends BaseMultiTenantModel
{
    protected $table            = 'accommodation_units';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tenant_id', 'type_id', 'parent_id', 'name', 'description', 'features_json', 'status', 'max_occupancy', 'bathrooms', 'beds_info', 'amenities'];
    protected $useTimestamps    = true;

    /**
     * Actualiza el estado de una unidad y sincroniza la jerarquía (Padres e Hijos)
     * para evitar overbooking en cabañas fraccionadas.
     *
     * @param int $unitId
     * @param string $status ('available', 'occupied', 'maintenance', 'blocked')
     * @return bool
     */
    public function updateStatusWithHierarchy(int $unitId, string $status): bool
    {
        $unit = $this->find($unitId);

        if (!$unit) {
            log_message('error', "[AccommodationUnitModel] updateStatusWithHierarchy: Unidad ID {$unitId} no encontrada.");
            return false;
        }

        // CI4 soporta transacciones anidadas, seguro usar transStart aquí
        $this->db->transStart();

        // 1. Actualizar el estado de la unidad solicitada
        $this->update($unitId, ['status' => $status]);
        log_message('info', "[AccommodationUnitModel] Unidad {$unitId} ({$unit['name']}) actualizada a estado: {$status}");

        // Estado derivado para la familia: Si me ocupo, bloqueo a mis familiares. Si me libero, ellos quedan disponibles.
        $derivedStatus = ($status === 'available') ? 'available' : 'blocked';

        // 2. Propagación HACIA ARRIBA (Si soy una habitación y tengo una Cabaña Padre)
        if (!empty($unit['parent_id'])) {
            if ($status === 'available') {
                // Si me libero, debo verificar si mis "hermanos" también están libres antes de liberar al Padre
                $occupiedSiblings = $this->where('parent_id', $unit['parent_id'])
                    ->where('id !=', $unitId)
                    ->whereIn('status', ['occupied', 'maintenance', 'blocked'])
                    ->countAllResults();

                if ($occupiedSiblings === 0) {
                    $this->update($unit['parent_id'], ['status' => 'available']);
                    log_message('info', "[AccommodationUnitModel] Padre ID {$unit['parent_id']} liberado (Todos los hijos disponibles).");
                }
            } else {
                // Si me ocupan, el Padre se bloquea instantáneamente
                $this->update($unit['parent_id'], ['status' => $derivedStatus]);
                log_message('info', "[AccommodationUnitModel] Padre ID {$unit['parent_id']} bloqueado por ocupación del hijo ID {$unitId}.");
            }
        }

        // 3. Propagación HACIA ABAJO (Si soy una Cabaña Padre y tengo Habitaciones Hijas)
        $children = $this->where('parent_id', $unitId)->findAll();

        if (!empty($children)) {
            foreach ($children as $child) {
                // Liberar hijos solo si estaban bloqueados (no si estaban en mantenimiento propio)
                if ($status === 'available' && $child['status'] === 'blocked') {
                    $this->update($child['id'], ['status' => 'available']);
                }
                // Bloquear hijos si estaban disponibles
                elseif ($status !== 'available' && $child['status'] === 'available') {
                    $this->update($child['id'], ['status' => $derivedStatus]);
                }
            }
            log_message('info', "[AccommodationUnitModel] Sincronizados " . count($children) . " hijos de la unidad Padre ID {$unitId}.");
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('critical', "[AccommodationUnitModel] Falló la transacción al actualizar la jerarquía para la unidad ID {$unitId}.");
            return false;
        }

        return true;
    }

    /**
     * Obtiene una unidad completa con toda su jerarquía:
     * Amenidades propias, Habitaciones hijas, Camas de las hijas y Amenidades de las hijas.
     *
     * @param int $unitId
     * @return array|null
     */
    public function getUnitWithHierarchy(int $unitId): ?array
    {
        $unit = $this->find($unitId);
        if (!$unit) return null;

        $db = \Config\Database::connect();

        // 1. Obtener amenidades del padre
        $parentAmenities = $db->table('unit_amenities')
            ->where('unit_id', $unitId)
            ->get()->getResultArray();
        $unit['amenities_list'] = array_column($parentAmenities, 'amenity_id');

        // 2. Obtener habitaciones hijas
        $rooms = $this->where('parent_id', $unitId)->findAll();

        foreach ($rooms as &$room) {
            // Camas de la habitación
            $room['beds'] = $db->table('unit_beds')
                ->where('unit_id', $room['id'])
                ->get()->getResultArray();

            // Amenidades de la habitación
            $roomAmenities = $db->table('unit_amenities')
                ->where('unit_id', $room['id'])
                ->get()->getResultArray();
            $room['amenities_list'] = array_column($roomAmenities, 'amenity_id');
        }

        $unit['rooms'] = $rooms;

        return $unit;
    }

}
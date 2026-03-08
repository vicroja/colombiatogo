<?php

namespace App\Controllers;

use App\Models\MaintenanceTaskModel;
use App\Models\AccommodationUnitModel;

class MaintenanceController extends BaseController
{
    public function index()
    {
        $taskModel = new MaintenanceTaskModel();
        $unitModel = new AccommodationUnitModel();

        // Traemos todas las tareas con el nombre de su habitación
        $allTasks = $taskModel->select('maintenance_tasks.*, accommodation_units.name as unit_name')
            ->join('accommodation_units', 'accommodation_units.id = maintenance_tasks.unit_id', 'left')
            ->orderBy('maintenance_tasks.created_at', 'DESC')
            ->findAll();

        // Las organizamos por estado para el Tablero Kanban
        $kanban = [
            'pending'     => [],
            'in_progress' => [],
            'completed'   => []
        ];

        foreach ($allTasks as $task) {
            $kanban[$task['status']][] = $task;
        }

        $units = $unitModel->findAll();

        return view('maintenance/index', [
            'kanban' => $kanban,
            'units'  => $units
        ]);
    }

    public function store()
    {
        $taskModel = new MaintenanceTaskModel();
        $unitModel = new AccommodationUnitModel();

        $blocksUnit = $this->request->getPost('blocks_unit') ? 1 : 0;
        $unitId = $this->request->getPost('unit_id');

        $taskModel->db->transStart();

        // 1. Crear la tarea
        $taskModel->createForTenant([
            'unit_id'        => $unitId ?: null,
            'title'          => $this->request->getPost('title'),
            'description'    => $this->request->getPost('description'),
            'priority'       => $this->request->getPost('priority'),
            'scheduled_date' => $this->request->getPost('scheduled_date') ?: date('Y-m-d'),
            'blocks_unit'    => $blocksUnit,
            'status'         => 'pending'
        ]);

        // 2. Si bloquea la unidad, la pasamos a estado 'maintenance'
        if ($blocksUnit && $unitId) {
            $unitModel->update($unitId, ['status' => 'maintenance']);
        }

        $taskModel->db->transComplete();

        return redirect()->to('/maintenance')->with('success', 'Tarea de mantenimiento registrada.');
    }

    public function updateStatus($id)
    {
        $taskModel = new MaintenanceTaskModel();
        $unitModel = new AccommodationUnitModel();

        $task = $taskModel->find($id);
        if (!$task) return redirect()->back();

        $newStatus = $this->request->getPost('status');

        $taskModel->db->transStart();

        // Actualizamos la tarea
        $taskModel->update($id, ['status' => $newStatus]);

        // Si la tarea se completó y bloqueaba la unidad, la liberamos
        if ($newStatus == 'completed' && $task['blocks_unit'] && $task['unit_id']) {
            // (En un sistema complejo verificaríamos si hay OTRAS tareas bloqueándola, para el MVP la liberamos)
            $unitModel->update($task['unit_id'], ['status' => 'available']);
        }

        $taskModel->db->transComplete();

        return redirect()->to('/maintenance')->with('success', 'Estado de la tarea actualizado.');
    }

    public function delete($id)
    {
        $taskModel = new MaintenanceTaskModel();
        $taskModel->delete($id);
        return redirect()->to('/maintenance')->with('success', 'Tarea eliminada.');
    }
}
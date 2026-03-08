<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use App\Models\PaymentModel;
use App\Models\SaleModel;

class ReportController extends BaseController
{
    public function index()
    {
        return view('reports/index');
    }

    public function export()
    {
        $reportType = $this->request->getPost('report_type');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');

        if (!$reportType || !$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Por favor completa todos los campos.');
        }

        // Preparar el archivo CSV
        $filename = "reporte_{$reportType}_" . date('Ymd_His') . ".csv";

        // Cabeceras HTTP para forzar la descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Abrir salida a php://output
        $output = fopen('php://output', 'w');
        // BOM para que Excel lea las tildes y eñes correctamente en UTF-8
        fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

        if ($reportType == 'reservations') {
            $this->exportReservations($output, $startDate, $endDate);
        } elseif ($reportType == 'income') {
            $this->exportIncome($output, $startDate, $endDate);
        } elseif ($reportType == 'open_accounts') {
            $this->exportOpenAccounts($output); // Este no filtra por fecha, muestra todo lo pendiente
        }

        fclose($output);
        exit(); // Terminamos la ejecución para que no imprima la vista HTML debajo del CSV
    }

    private function exportReservations($output, $startDate, $endDate)
    {
        fputcsv($output, ['ID Reserva', 'Huesped', 'Documento', 'Habitacion', 'Check-in', 'Check-out', 'Noches', 'Total', 'Estado']);

        $db = \Config\Database::connect();
        $builder = $db->table('reservations');
        $builder->select('reservations.id, guests.full_name, guests.document, accommodation_units.name as unit_name, reservations.check_in_date, reservations.check_out_date, reservations.nights, reservations.total_price, reservations.status');
        $builder->join('guests', 'guests.id = reservations.guest_id');
        $builder->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id');
        $builder->where('reservations.tenant_id', session('active_tenant_id'));
        $builder->groupStart()
            ->where("reservations.check_in_date >=", $startDate)
            ->where("reservations.check_in_date <=", $endDate)
            ->groupEnd();

        $reservations = $builder->get()->getResultArray();

        foreach ($reservations as $res) {
            fputcsv($output, [
                $res['id'],
                $res['full_name'],
                $res['document'],
                $res['unit_name'],
                $res['check_in_date'],
                $res['check_out_date'],
                $res['nights'],
                $res['total_price'],
                $res['status']
            ]);
        }
    }

    private function exportIncome($output, $startDate, $endDate)
    {
        fputcsv($output, ['Fecha Pago', 'Recibo/Ref', 'Reserva ID', 'Metodo', 'Monto Registrado']);

        $db = \Config\Database::connect();
        $builder = $db->table('payments');
        $builder->select('created_at, reference, reservation_id, payment_method, amount');
        // Filtramos por las reservas de este tenant (usando una subconsulta o join simple)
        $builder->join('reservations', 'reservations.id = payments.reservation_id');
        $builder->where('reservations.tenant_id', session('active_tenant_id'));
        $builder->where('DATE(payments.created_at) >=', $startDate);
        $builder->where('DATE(payments.created_at) <=', $endDate);
        $builder->orderBy('payments.created_at', 'ASC');

        $payments = $builder->get()->getResultArray();
        $total = 0;

        foreach ($payments as $pay) {
            fputcsv($output, [
                date('d/m/Y H:i', strtotime($pay['created_at'])),
                $pay['reference'] ?: 'N/A',
                $pay['reservation_id'],
                $pay['payment_method'],
                $pay['amount']
            ]);
            $total += $pay['amount'];
        }

        fputcsv($output, ['', '', '', 'TOTAL INGRESOS:', $total]);
    }

    private function exportOpenAccounts($output)
    {
        // Trae las reservas que no estén canceladas ni check_out y tengan saldo
        fputcsv($output, ['Reserva ID', 'Huesped', 'Total Alojamiento + Consumos', 'Abonado', 'Saldo Pendiente', 'Estado Actual']);

        $db = \Config\Database::connect();
        $builder = $db->table('reservations');
        $builder->select('reservations.id, guests.full_name, reservations.total_price, reservations.status');
        $builder->join('guests', 'guests.id = reservations.guest_id');
        $builder->where('reservations.tenant_id', session('active_tenant_id'));
        $builder->whereNotIn('reservations.status', ['cancelled', 'checked_out']);

        $reservations = $builder->get()->getResultArray();

        $paymentModel = new PaymentModel();
        $consumptionModel = new \App\Models\ReservationConsumptionModel();

        foreach ($reservations as $res) {
            $totalPaid = array_sum(array_column($paymentModel->where('reservation_id', $res['id'])->findAll(), 'amount'));
            $totalConsumptions = array_sum(array_column($consumptionModel->where('reservation_id', $res['id'])->findAll(), 'subtotal'));

            $grandTotal = $res['total_price'] + $totalConsumptions;
            $balance = $grandTotal - $totalPaid;

            if ($balance > 0) {
                fputcsv($output, [
                    $res['id'],
                    $res['full_name'],
                    $grandTotal,
                    $totalPaid,
                    $balance,
                    $res['status']
                ]);
            }
        }
    }
}
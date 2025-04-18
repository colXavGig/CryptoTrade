<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\ReportService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\CSRFService;
use Exception;

class ReportController
{
    public function getUserReport(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $reportService = new ReportService();
            $report = $reportService->getUserReport($authUser['user_id']);

            echo json_encode(['success' => true, 'report' => $report]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getAdminReport(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            if ($authUser['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }

            $reportService = new ReportService();
            $report = $reportService->getAdminReport();

            echo json_encode(['success' => true, 'report' => $report]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function downloadPDFReport(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $type = $_POST['type'] ?? 'user';
            $reportService = new ReportService();

            ob_start();
            if ($type === 'admin' && $authUser['role'] === 'admin') {
                $report = $reportService->getAdminReport();
                include __DIR__ . '/../../views/pdf/admin_report_pdf.php';
            } else {
                include __DIR__ . '/../../views/pdf/user_report_pdf.php';
            }
            $html = ob_get_clean();

            $reportService->generatePDF($html);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
    }

    private function sendError(Exception $e): void
    {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

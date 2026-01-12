<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Acesso não autorizado.');
        }
        
        return view('admin.dashboard');
    }

    public function storeCampaign(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Acesso não autorizado.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'discount_type' => 'required|in:PERCENTAGE,FIXED',
                'discount_value' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'priority' => 'required|integer|min:1|max:5',
                'attribute' => 'required|in:PRICE,DURATION,AIRLINE',
                'operator' => 'required|in:>,<,=,>=,<=',
                'value' => 'required|string',
            ]);

            $name = $request->name;
            $description = $request->description;
            $discountType = $request->discount_type;
            $discountValue = $request->discount_value;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $priority = $request->priority;
            $attribute = $request->attribute;
            $operator = $request->operator;
            $value = $request->value;

            // Log the parameters
            Log::info('Creating new campaign:', [
                'name' => $name,
                'description' => $description,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'priority' => $priority,
                'attribute' => $attribute,
                'operator' => $operator,
                'value' => $value
            ]);

            // Call the stored procedure
            DB::statement('CALL insert_campaign_with_condition(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $name,
                $description,
                $discountType,
                $discountValue,
                $startDate,
                $endDate,
                $priority,
                $attribute,
                $operator,
                $value
            ]);

            Log::info('Campaign created successfully: ' . $name);

            return response()->json([
                'success' => true,
                'message' => 'Campanha criada com sucesso!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar campanha: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar campanha: ' . $e->getMessage()
            ], 500);
        }
    }
}

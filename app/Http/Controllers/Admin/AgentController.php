<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentSale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function agents()
    {
        $agents = Agent::withSum('sales', 'commission_amount')
            ->withSum('sales', 'quantity')
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        return view('admin.pages.agents', compact('agents'));
    }

    public function addAgent(Request $request)
    {
        $data = [];

        if ($request->id) {
            $data['agent'] = Agent::findOrFail($request->id);
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'type' => 'required|in:vendor,referrer',
                'commission_rate' => 'required|numeric|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            if ($request->id) {
                $agent = Agent::findOrFail($request->id);
                $message = 'Agent updated successfully';
            } else {
                $agent = new Agent();
                $agent->referral_code = $this->generateReferralCode($request->name);
                $message = 'Agent added successfully';
            }

            $agent->fill($request->only('name', 'email', 'phone', 'type', 'commission_rate', 'notes'));
            $agent->is_active = $request->boolean('is_active', true);
            $agent->save();

            return redirect()->route('admin.agents')->with('success', $message);
        }

        return view('admin.pages.addAgent', compact('data'));
    }

    private function generateReferralCode(string $name): string
    {
        $base = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name) ?: 'AGT', 0, 4));

        do {
            $code = $base . '-' . Str::upper(Str::random(4));
        } while (Agent::where('referral_code', $code)->exists());

        return $code;
    }

    public function deleteAgent(Request $request)
    {
        Agent::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Agent deleted successfully']);
    }

    public function agentSales(Request $request)
    {
        $sales = AgentSale::with(['agent', 'product'])
            ->when($request->filled('agent_id'), fn ($q) => $q->where('agent_id', $request->agent_id))
            ->latest('sale_date')
            ->paginate(15)
            ->withQueryString();

        $agents = Agent::orderBy('name')->get();

        return view('admin.pages.agentSales', compact('sales', 'agents'));
    }

    public function addAgentSale(Request $request)
    {
        $data['agents'] = Agent::where('is_active', true)->orderBy('name')->get();
        $data['products'] = Product::orderBy('name')->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'agent_id' => 'required|exists:agents,id',
                'product_id' => 'nullable|exists:products,id',
                'product_name' => 'required_without:product_id|nullable|string|max:255',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
                'commission_rate' => 'required|numeric|min:0|max:100',
                'sale_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $product = $request->product_id ? Product::find($request->product_id) : null;
            $productName = $product?->name ?? $request->product_name;

            $totalAmount = $request->quantity * $request->unit_price;
            $commissionAmount = round($totalAmount * ($request->commission_rate / 100), 2);

            AgentSale::create([
                'agent_id' => $request->agent_id,
                'product_id' => $product?->id,
                'product_name' => $productName,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_amount' => $totalAmount,
                'commission_rate' => $request->commission_rate,
                'commission_amount' => $commissionAmount,
                'sale_date' => $request->sale_date,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.agent-sales')->with('success', 'Sale logged successfully');
        }

        return view('admin.pages.addAgentSale', compact('data'));
    }

    public function deleteAgentSale(Request $request)
    {
        AgentSale::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Sale record deleted successfully']);
    }
}

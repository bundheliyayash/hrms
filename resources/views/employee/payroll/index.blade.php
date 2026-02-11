<x-admin-layout>
    <x-slot name="header">
        My Payroll History
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month/Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($payrolls as $payroll)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $payroll->month }} {{ $payroll->year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($payroll->basic_salary, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-green-600">₹{{ number_format($payroll->net_salary, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('employee.payroll.show', $payroll->id) }}" class="text-indigo-600 hover:text-indigo-900">View Payslip</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                         {{ $payrolls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

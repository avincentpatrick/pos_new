<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    Admin Modules
                </h1>
                <p class="mt-2 text-gray-600">
                    Select a module to manage your application.
                </p>
            </div>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <!-- Products -->
                <a href="{{ route('products') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-box text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Products</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Manage inventory</p>
                </a>
                <!-- Clients -->
                <a href="{{ route('clients') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-user-plus text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Clients</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Manage customers</p>
                </a>
                <!-- Users -->
                <a href="{{ route('users') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-users text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Users</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Manage accounts</p>
                </a>
                <!-- Expenses -->
                <a href="{{ route('expenses') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-file-invoice-dollar text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Expenses</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Track spending</p>
                </a>
                <!-- Sales -->
                <a href="#" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-chart-line text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Sales</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">View reports</p>
                </a>
                <!-- Payroll -->
                <a href="#" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-money-check-dollar text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Payroll</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Process salaries</p>
                </a>
                <!-- Employees -->
                <a href="{{ route('personnel') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-user-tie text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Personnel</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Manage staff</p>
                </a>
                <!-- Transactions -->
                <a href="{{ route('transactions') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-receipt text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Transactions</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">View all transactions</p>
                </a>
                <!-- Stock Movement Monitoring -->
                <a href="{{ route('stock-movement-monitoring') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-truck-ramp-box text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Stock Movement</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Monitor stock history</p>
                </a>
                <!-- Promos and Discounts -->
                <a href="{{ route('promos-and-discounts') }}" class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-tags text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Promos & Discounts</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Manage promotions</p>
                </a>
            </div>
        </div>
    </div>
</div>

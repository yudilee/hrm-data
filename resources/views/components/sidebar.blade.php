<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
      <div class="flex items-center ps-2.5 mb-8">
         <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-3 shadow-lg shadow-indigo-500/50">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
         </div>
         <span class="self-center text-xl font-bold whitespace-nowrap dark:text-white tracking-tight">RTS <span class="text-indigo-600">Master</span></span>
      </div>
      <ul class="space-y-2 font-medium">
         <li>
            <a href="{{ route('home') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('home') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}">
               <svg class="w-5 h-5 transition duration-75 {{ request()->routeIs('home') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
               </svg>
               <span class="ms-3">Dashboard</span>
            </a>
         </li>
         <li>
            <a href="{{ route('master-vehicles.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('master-vehicles.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}">
               <svg class="w-5 h-5 transition duration-75 {{ request()->routeIs('master-vehicles.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
               </svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Master Vehicles</span>
            </a>
         </li>
         <li>
            <a href="{{ route('service-history.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('service-history.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}">
               <svg class="w-5 h-5 transition duration-75 {{ request()->routeIs('service-history.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
               </svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Service History</span>
            </a>
         </li>
         <li>
            <a href="{{ route('master-customers.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('master-customers.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}">
               <svg class="w-5 h-5 transition duration-75 {{ request()->routeIs('master-customers.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
               </svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Master Customers</span>
            </a>
         </li>
         <li>
            <a href="{{ route('import.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('import.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}">
               <svg class="w-5 h-5 transition duration-75 {{ request()->routeIs('import.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
               </svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Import Data</span>
               <span class="inline-flex items-center justify-center px-2 ms-3 text-xs font-medium text-gray-800 bg-gray-200 rounded-full dark:bg-gray-700 dark:text-gray-300">New</span>
            </a>
         </li>
         <li class="pt-4 mt-4 space-y-2 border-t border-gray-200 dark:border-gray-700">
            <span class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Labour Data</span>
            <a href="{{ route('labour-search') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('labour-search') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}">
               <svg class="w-5 h-5 transition duration-75 {{ request()->routeIs('labour-search') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
               </svg>
               <span class="ms-3">Labour Search (RTS)</span>
            </a>
         </li>
      </ul>
   </div>
</aside>

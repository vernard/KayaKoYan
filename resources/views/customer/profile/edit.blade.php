<x-layouts.app>
    <x-slot:title>Edit Profile</x-slot:title>

    <div class="py-12 bg-gray-50 min-h-[80vh]">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit Profile</h1>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                        <div class="flex items-center gap-6">
                            <div class="relative">
                                <img src="{{ $user->avatar_url }}"
                                     alt="{{ $user->name }}"
                                     class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
                                     id="avatar-preview">
                            </div>
                            <div class="flex-1">
                                <input type="file"
                                       name="avatar"
                                       id="avatar-input"
                                       accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                <p class="mt-1 text-sm text-gray-500">JPG, PNG or GIF. Max 20MB.</p>
                                @error('avatar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                @if($user->avatar_path)
                                    <button type="button"
                                            onclick="document.getElementById('delete-avatar-form').submit()"
                                            class="mt-2 text-sm text-red-600 hover:text-red-700">
                                        Remove photo
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email"
                               value="{{ $user->email }}"
                               disabled
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                        <p class="mt-1 text-sm text-gray-500">Email cannot be changed.</p>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('customer.orders.index') }}"
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h2>

                @if(session('password_success'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        {{ session('password_success') }}
                    </div>
                @endif

                <form action="{{ route('customer.profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password"
                               name="current_password"
                               id="current_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="delete-avatar-form" action="{{ route('customer.profile.delete-avatar') }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-layouts.app>

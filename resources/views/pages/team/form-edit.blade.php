@extends('layouts.main')
@section('title', 'Chỉnh sửa đội thi')
@section('page-title', 'Chỉnh sửa đội thi')
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-flush h-lg-100 p-10">
                <form id="formTeam" action="{{ route('admin.teams.update', ['id' => $team->id]) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group mb-10">
                                <label class="form-label" for="">Tên đội thi</label>
                                <input type="text" name="name" value="{{ old('name', $team->name) }}"
                                    class=" form-control" placeholder="">
                                @error('name')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                @if (session()->has('errorName'))
                                    <p class="text-danger">{{ session()->get('errorName') }}</p>
                                    @php
                                        Session::forget('errorName');
                                    @endphp
                                @endif
                            </div>
                            <div class="form-group mb-10">
                                <label for="" class="form-label">Thuộc cuộc thi</label>
                                <select class="form-select mb-2 select2-hidden-accessible" data-control="select2"
                                    data-hide-search="false" data-placeholder="Select an option" tabindex="-1"
                                    aria-hidden="true" name="contest_id">
                                    <option data-select2-id="select2-data-130-vofb"></option>
                                    @foreach ($contests as $contest)
                                        <option value="{{ $contest->id }}"
                                            {{ $team->contest_id === $contest->id ? 'selected' : '' }}>
                                            {{ $contest->name }}</option>
                                    @endforeach
                                </select>
                                @error('contest_id')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group list-group mb-5">
                                <label class="form-label" for="">Thành viên nhóm</label>
                                <div class="input-group mb-3">
                                    <input placeholder="Hãy nhập email hoặc tên để tìm kiếm..." type="text"
                                        class="form-control" id="searchUserValue">
                                    <button id="searchUser" class="btn btn-secondary rounded-end" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Tìm</button>
                                    <ul id="resultUserSearch" class="dropdown-menu dropdown-menu-end w-500px">
                                    </ul>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="listUser">
                                        <h4>Danh sách chờ</h4>
                                        <div id="resultArrayUser" class=" mt-4">
                                        </div>
                                        <p class="text-danger" id="mesArrayUser">
                                            @if (session()->has('error'))
                                                {{ session()->get('error') }}
                                                @php
                                                    Session::forget('error');
                                                @endphp
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group ">
                                <label for="" class="form-label">Ảnh đội thi</label>
                                <input value="{{ old('image', $team->image) }}" name="image" type='file' id="file-input"
                                    accept=".png, .jpg, .jpeg" class="form-control" />
                                @error('image')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <img class="w-100 mt-4 border rounded-3" id="image-preview"
                                    src="{{ Storage::disk('s3')->has($team->image) ? Storage::disk('s3')->temporaryUrl($team->image, now()->addMinutes(5)) : 'https://vanhoadoanhnghiepvn.vn/wp-content/uploads/2020/08/112815953-stock-vector-no-image-available-icon-flat-vector.jpg' }} " />
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-10 ">
                        <button type="submit" id="buttonTeam" class="btn btn-success btn-lg btn-block">Lưu </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if (session()->has('userArray'))
        @php
            $userArray = session()->get('userArray');
            Session::forget('userArray');
        @endphp
    @endif
@endsection

@section('page-script')
    <script src="assets/js/system/preview-file/previewImg.js"></script>
    <script src="{{ asset('assets/js/system/team/validateForm.js') }}"></script>
    <script>
        preview.showFile('#file-input', '#image-preview');
        var userArray = @json($userArray ?? []);
        var _token = "{{ csrf_token() }}"
        var max_user = "{{ $contest->max_user }}"
        var urlSearch = "{{ route('admin.user.TeamUserSearch') }}"
        var urlShowContest = "{{ route('admin.teams.add.contest.show') }}";
    </script>
    <script src="{{ asset('assets/js/system/validate/validate.js') }}"></script>
    <script src="{{ asset('assets/js/system/team/add.js') }}"></script>
    <script src="{{ asset('assets/js/system/team/team.js') }}"></script>
@endsection

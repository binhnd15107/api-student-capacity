@extends('layouts.main')
@section('title', 'Thêm đội thi trong cuộc thi')
@section('page-title', 'Thêm đội thi trong cuộc thi')
@section('content')
    <div class="card card-flush h-lg-100 p-10">
        <div class="row mb-10">
            <div class="col-lg-12">
                <ol class="breadcrumb text-muted fs-6 fw-bold">
                    <li class="breadcrumb-item pe-3">
                        <a href="{{ route('admin.contest.list') }}" class="pe-3">Cuộc
                            thi</a>
                    </li>
                    <li class="breadcrumb-item px-3 ">
                        <a href="{{ route('admin.contest.show', ['id' => $contest->id]) }}" class="pe-3">
                            {{ $contest->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item px-3 ">
                        <a href="{{ route('admin.contest.detail.team', ['id' => $contest->id]) }}">
                            Danh sách đội thi thuộc cuộc thi : {{ $contest->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item px-3 text-muted">
                        Thêm đội thi của cuộc thi : {{ $contest->name }}
                    </li>
                </ol>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-12">

                <form id="formTeam" action="{{ route('admin.contest.detail.team.add.save', ['id' => $contest->id]) }}"
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group mb-10">
                                <label class="form-label" for="">Tên đội thi</label>
                                <input type="text" name="name" value="{{ old('name') }}" class=" form-control"
                                    placeholder="">
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

                            <div class="form-group list-group mb-5">
                                <label class="form-label" for="">Thành viên nhóm</label>
                                <div class="input-group mb-3">
                                    <input placeholder="Hãy nhập email hoặc tên để tìm kiếm..." type="text"
                                        class="form-control" id="searchUserValue">
                                    {{-- <button id="searchUser" class="btn btn-secondary rounded-end" type="button">Tìm</button> --}}
                                    <button id="searchUser" type="button" class="btn btn-primary">Tìm</button>

                                </div>
                                <ul id="resultUserSearch">
                                </ul>

                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <hr>
                                    <div>

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
                                <input value="{{ old('image') }}" name="image" type='file' id="file-input"
                                    accept=".png, .jpg, .jpeg" class="form-control" />
                                @error('image')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <img class="w-100 mt-4 border rounded-3" id="image-preview"
                                    src="https://vanhoadoanhnghiepvn.vn/wp-content/uploads/2020/08/112815953-stock-vector-no-image-available-icon-flat-vector.jpg" />
                            </div>
                        </div>

                    </div>

                    <div class="form-group mt-10 ">
                        <button disabled type="submit" id="buttonTeam" class="btn btn-success btn-lg btn-block">Lưu
                        </button>
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
        rules.image = {
            required: true,
        }
        messages.image = {
            required: "Chưa nhập trường này !",
        }
        preview.showFile('#file-input', '#image-preview');
        var userArray = @json($userArray ?? []);
        var id_contest = 0;

        var _token = "{{ csrf_token() }}"
        var max_user = "{{ $contest->max_user }}"
        var urlSearch = "{{ route('admin.user.team.search', ['id_contest' => $contest->id]) }}"
    </script>
    <script src="{{ asset('assets/js/system/validate/validate.js') }}"></script>
    <script src="{{ asset('assets/js/system/team/team.js') }}"></script>
@endsection

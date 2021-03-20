@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="border rounded">
      <div class="container my-3">
        @if (isset($publication))
            <form action="{{ route('publications.update', $publication->id) }}" method="post">
            @method('PUT')
        @else
            <form action="{{ route('publications.store') }}" method="post">
        @endif
          @csrf
          <div class="row">
            <div class="form-group col-md-4">
              <label for="">Resume</label>
              <select name="resume_id" class="form-control" id="resume">
                @foreach ($resumes as $resume)
                  @if (isset($publication) && $publication->resume->id === $resume->id)
                    <option selected value="{{ $resume->id }}">{{ $resume->title }}</option>
                  @else
                    <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-4">
              <label for="">Theme</label>
              <select name="theme_id" class="form-control" id="theme">
                @foreach ($themes as $theme)
                  {{-- <option value="{{ $theme->id }}"> {{ $theme->theme }} </option> --}}
                  @if (isset($publication) && $publication->theme->id === $theme->id)
                    <option selected value="{{ $theme->id }}">{{ $theme->theme }}</option>
                  @else
                    <option value="{{ $theme->id }}">{{ $theme->theme }}</option>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-4">
              <label for="">Visibility</label>
              <select name="visibility" class="form-control">
                @foreach (['public', 'private', 'hidden'] as $visibility)
                  {{-- <option value="{{ $visibility }}"> {{ $visibility }} </option> --}}
                  @if (isset($publication) && $publication->visibility === $visibility)
                    <option selected value="{{ $visibility }}"> {{ $visibility }} </option>
                  @else
                    <option value="{{ $visibility }}"> {{ $visibility }} </option>
                  @endif
                @endforeach
              </select>
            </div>
          </div>
          <div class="mx-auto" style="width: 80px;">
            <button class="btn btn-primary" style="width: 80px" type="submit">Save</button>
          </div>
        </form>
      </div>
    </div>
    <div>
      <iframe id="iframe" class="border rounded w-100" style="height: 640px" src="" frameborder="0"></iframe>
    </div>
  </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const iframe = document.getElementById('iframe')

        async function loadPreview(resume, theme) {
            iframe.srcdoc = '<h1>Loading Preview...</h1>'
            try {
                const res = await axios.post(route('publications.preview'), {
                    resume_id: resume,
                    theme_id: theme
                })

                iframe.srcdoc = res.data
            } catch (e) {
                console.error(e);
            }
        }
        const resume = document.getElementById('resume')
        const theme = document.getElementById('theme')

        await loadPreview(resume.value, theme.value)

        resume.onchange = async (e) => await loadPreview(e.target.value, theme.value)
        theme.onchange = async (e) => await loadPreview(resume.value, e.target.value)
    })
</script>
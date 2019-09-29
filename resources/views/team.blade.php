@extends('layouts.app')

@section('content')
    @yield('match_content')

    <table id="players">
        <thead>
            <tr>
                <th>#</th>
                <th>Игрок</th>
                <th>Поз.</th>
                <th>Скор.</th>
                <th>Ускор.</th>
                <th>Коор.</th>
                <th>Сила.</th>
                <th>Точн.</th>
                <th>Вид.</th>
                <th>Реак.</th>
                <th>В ств.</th>
                <th>На вых.</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($players as $player)
                <tr data-id="{{ $player->id }}">
                    <td>{{ $player->id }}</td>
                    <td>{{ $player->name }}</td>
                    <td>{{ $player->roles->implode(', ') }}</td>
                    <td>{{ $player->speed }}</td>
                    <td>{{ $player->acceleration }}</td>
                    <td>{{ $player->coordination }}</td>
                    <td>{{ $player->power }}</td>
                    <td>{{ $player->accuracy }}</td>
                    <td>{{ $player->vision }}</td>
                    <td>{{ $player->reaction }}</td>
                    <td>{{ $player->in_gate }}</td>
                    <td>{{ $player->on_out }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <form id="schemaForm">
        <div id="controls">
            <select name="schema[id]">
                @foreach ($schemas as $schema)
                    <option
                        value="{{ $schema->id }}"
                        {!! $currentSchema->id == $schema->id ? ' selected' : '' !!}
                    >{{ $schema->name }}</option>
                @endforeach
            </select>
            <a id="removeSchema"{!! $schemas->count() < 2 ? ' class="display-none"' : '' !!} href="#">Удалить</a>
            <a id="saveSchema" class="display-none" href="#">Сохранить</a>
            <a id="createSchemaOpen" href="#">Сохранить как</a>
            <a id="confirmSchema" class="display-none" href="#">Принять</a>
        </div>

        @foreach (config('schema.options') as $option => $settings)
            {{ $option }}:
            <select name="schema[settings][{{ $option }}]">
                @foreach ($settings as $setting)
                    <option
                        value="{{ $setting }}"
                        {!! $currentSchema->settings->{$option} == $setting ? ' selected' : '' !!}
                    >{{ $setting }}</option>
                @endforeach
            </select>
        @endforeach

        <div id="field">
            @foreach (config('player.role_areas') as $role => $coords)
                <div data-coords="{{ json_encode($coords) }}" class="role-area"><span>{{ $role }}</span></div>
            @endforeach

            @foreach ($players as $player)
                <span
                    data-id="{{ $player->id }}"
                    class="player{{ !$player->settings->position ? ' display-none' : '' }}"
                >{{ $player->id }}</span>
            @endforeach
        </div>

        @foreach ($players as $player)
            <input
                type="hidden"
                name="player_settings[{{ $player->id }}][position]"
                value="{{ $player->settings->position ? json_encode($player->settings->position) : 'NULL' }}"
            >
            <input
                type="hidden"
                name="player_settings[{{ $player->id }}][reserve_index]"
                value="{{ $player->settings->reserve_index === NULL ? 'NULL' : $player->settings->reserve_index }}"
            >
        @endforeach

        <div class="popup" id="createSchemaBlock">
            <div class="popup-content">
                Название:
                <input type="text" name="schema[name]">
                <a id="createSchema" href="#">Сохранить</a>
            </div>
        </div>
    </form>
@endsection

@section('css')
    <link href="{{ mix('css/team.css') }}" rel="stylesheet">
    @yield('match_css')
@endsection

@section('js')
    <script src="{{ mix('js/team.js') }}"></script>
    @yield('match_js')
@endsection

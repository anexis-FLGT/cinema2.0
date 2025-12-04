<table class="table table-dark table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>ФИО / Логин</th>
            <th>Телефон</th>
            <th>Роль</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->id_user }}</td>
                <td>{{ trim(($u->last_name ?? '') . ' ' . ($u->first_name ?? '')) }} <br><small class="text-muted">{{ $u->login }}</small></td>
                <td>{{ $u->phone }}</td>
                <td>{{ \Illuminate\Support\Facades\DB::table('roles')->where('id_role', $u->role_id)->value('role_name') ?? '—' }}</td>
                <td>
                    <button class="btn btn-sm btn-outline-light">Просмотр</button>
                    <button class="btn btn-sm btn-warning">Редактировать</button>
                    <button class="btn btn-sm btn-danger">Удалить</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

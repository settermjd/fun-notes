create table if not exists notes
(
    id      integer             constraint notes_pk primary key autoincrement,
    title   TEXT                not null,
    body    TEXT                not null,
    created TEXT default current_date not null
);

create index notes__idx__title
    on notes (title);

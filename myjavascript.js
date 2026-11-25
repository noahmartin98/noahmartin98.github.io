$(document).ready(function() {
  $('#leaders').DataTable({
    paging: false,
    info: false,
    searching: true,
    columnDefs: [
      {
        targets: '_all',
        orderSequence: ['desc', 'asc'] // First click sorts DESC
      }
    ]
  });

  // Move DataTables search box into the top header row if #table-header-bar exists
  if ($('#table-header-bar').length) {
    $('#leaders_filter').appendTo('#table-header-bar');
  }
});

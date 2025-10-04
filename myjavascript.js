
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
});
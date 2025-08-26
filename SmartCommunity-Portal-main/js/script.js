function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const iconExpand = document.getElementById('icon-expand');
  const iconCollapse = document.getElementById('icon-collapse');

  sidebar.classList.toggle('close');
  iconExpand.classList.toggle('hidden');
  iconCollapse.classList.toggle('hidden');
}
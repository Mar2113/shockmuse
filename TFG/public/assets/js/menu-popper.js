document.getElementById("menu-icon").addEventListener("click", function () {
	var sidebar = document.getElementById("sidebar");
	if (sidebar.style.left === "0px") {
		sidebar.style.left = "-250px";
	} else {
		sidebar.style.left = "0";
	}
});

document.addEventListener("DOMContentLoaded", function () {
	var menuItemsWithSubmenu = document.querySelectorAll('.menu-item-with-submenu');

	menuItemsWithSubmenu.forEach(function (menuItem) {
		menuItem.addEventListener('click', function () {
			var submenu = menuItem.querySelector('.submenu');
			var isActive = submenu.style.display === 'block';
			menuItemsWithSubmenu.forEach(function (item) {
				var submenuToHide = item.querySelector('.submenu');
				if (submenuToHide && submenuToHide !== submenu) {
					submenuToHide.style.display = 'none';
				}
			});
			submenu.style.display = isActive ? 'none' : 'block';
		});
	});
});
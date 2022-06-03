document.addEventListener('DOMContentLoaded', function (event)
{
	var backToTop = document.getElementById('toTop');

	if (backToTop)
	{
		function checkScrollPos() {
			if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20)
			{
				backToTop.classList.add('visible');
			}
			else
			{
				backToTop.classList.remove('visible')
			}
		}

		checkScrollPos();

		window.onscroll = function()
		{
			checkScrollPos();
		};
	}
});

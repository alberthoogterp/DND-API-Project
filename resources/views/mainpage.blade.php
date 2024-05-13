<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" type="text/css" href="/css/mainpage.css">

	<title>DnD info tool</title>
</head>

<body onload="initialize()">
	<div id="main">
		<h3>DnD info tool</h3>


		<div id="resultcontainer">
			<div id="previousCategories">
				<div id="searchcontainer">
					<div id="searchbar">
						<input type="button" id="homeButton" value="Home">
						<input type="button" id="previousButton" value="Previous">
						@if (session('error'))
							<span id="error">{{ session('error') }}</span>
						@endif
					</div>
				</div>
			</div>

			<div id="resultInfo"></div>
		</div>

		<form id="previousCategoriesform" action="{{ route('newResult') }}" method="GET">
			<input type="text" id="selectedResult" name="selectedResult" hidden>
		</form>
	</div>

	<script>
		const savedResults = {!! json_encode(session()->get('savedResults')) !!};
		const decisionTree = {!! json_encode(session()->get('decisionTree')) !!};
		const currentKey = decisionTree[decisionTree.length - 1];

		function initialize() {
			const homeButton = document.getElementById("homeButton");
			const previousButton = document.getElementById("previousButton");
			showPreviousCategories();
			showInfo();
			homeButton.addEventListener("click", homeButtonClick);
			previousButton.addEventListener("click", previousButtonClick);
			homeButton.addEventListener("mouseover", hover.bind(null, homeButton));
			previousButton.addEventListener("mouseover", hover.bind(null, previousButton));
			homeButton.addEventListener("mouseout", hoverLeave.bind(null, homeButton));
			previousButton.addEventListener("mouseout", hoverLeave.bind(null, previousButton));
		}

		function showPreviousCategories() {
			const previousCategoriesDiv = document.getElementById("previousCategories");
			for (category of decisionTree) {
				let button = createLinkButton(category, category);
				previousCategoriesDiv.appendChild(button);
			}
		}

		function showInfo() {
			const keyData = savedResults[currentKey];
			const descriptionBox = document.getElementById("resultInfo");
			for (item in keyData) {
				createElementFromJson(item, keyData[item], descriptionBox);
			}
		}

		function createElementFromJson(key, value, parentElement) {
			if (key == "count" || key == "index") {
				return;
			}

			let caseHit = false;
			switch (typeof value) {
				case "string":
					if (isUrl(value)) {
						if (value != currentKey) {
							let newButton = createLinkButton(value, value);
							parentElement.appendChild(newButton);
							parentElement.appendChild(document.createElement("br"));
						}
					} else {
						addKeySpan(key, parentElement);
						let newSpan = document.createElement("span");
						let text = document.createTextNode(value);
						newSpan.appendChild(text);
						parentElement.appendChild(newSpan);
						parentElement.appendChild(document.createElement("br"));
					}
					break;
				case "number":
					addKeySpan(key, parentElement);
					let newSpan = document.createElement("span");
					let text = document.createTextNode(value);
					newSpan.appendChild(text);
					parentElement.appendChild(newSpan);
					parentElement.appendChild(document.createElement("br"));
					break;
				case "object":
					if (value instanceof Array) {
						if (key == "desc") {
							let valueDiv = document.createElement("div");
							valueDiv.setAttribute("class", "valueDiv");
							let descDiv = document.createElement("div");
							descDiv.setAttribute("class", "descDiv");
							for (i = 0; i <= value.length; i++) {
								createElementFromJson(i, value[i], descDiv);
							}
							valueDiv.appendChild(descDiv);
							parentElement.appendChild(valueDiv);
							parentElement.appendChild(document.createElement("br"));
						} else {
							let valueDiv = document.createElement("div");
							valueDiv.setAttribute("class", "valueDiv");
							addKeySpan(key, parentElement);
							for (i = 0; i <= value.length; i++) {
								createElementFromJson(i, value[i], valueDiv);
							}
							parentElement.appendChild(valueDiv);
						}
					} else if (value instanceof Object) {
						addKeySpan(key, parentElement);
						let valueDiv = document.createElement("div");
						valueDiv.setAttribute("class", "valueDiv");
						for (key in value) {
							createElementFromJson(key, value[key], valueDiv);
						}
						parentElement.appendChild(valueDiv);
					}
					break;
			}
		}

		function addKeySpan(key, parentElement) {
			if (isNaN(key)) {
				let newSpan = document.createElement("span");
				let text = document.createTextNode(key + " : ");
				newSpan.appendChild(text);
				parentElement.appendChild(newSpan);
			}
		}

		function isUrl(string) {
			const regExp = new RegExp("^\/api\/");
			let found = string.match(regExp);
			if (found) {
				return true;
			}
			return false;
		}

		function createLinkButton(buttonValue, url) {
			const button = document.createElement("input");
			button.setAttribute("type", "button");
			button.setAttribute("value", buttonValue);
			button.setAttribute("data-url", url);
			button.addEventListener("mouseover", hover.bind(null, button));
			button.addEventListener("mouseout", hoverLeave.bind(null, button));
			button.addEventListener("click", resultElementClick.bind(null, button));
			return button;
		}

		function homeButtonClick(event) {
			sendForm("home");
		}

		function previousButtonClick(event) {
			sendForm("previous");
		}

		function hover(element, event) {
			element.style.borderColor = "black";
			element.style.backgroundColor = "lightgrey"
		}

		function hoverLeave(element, event) {
			element.style.backgroundColor = "transparent";
		}

		function resultElementClick(element, event) {
			sendForm(element.dataset.url);
		}

		function sendForm(formValue) {
			let form = document.getElementById("previousCategoriesform");
			let forminput = form.querySelector("#selectedResult");
			forminput.setAttribute("value", formValue);
			form.submit();
		}
	</script>
</body>

</html>

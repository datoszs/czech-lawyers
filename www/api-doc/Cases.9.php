<!DOCTYPE html>
<html>
<head>
	<title>ApiDocu - /api/case/&lt;id&gt;</title>
	<style>html, body {
	margin: 0;
	padding: 0;
	height: 100%;
	width: 100%;
	display: table;
}

body {
	background-color: #2980b9;
	text-align: center;
	font-size: 14px;
	color: #2c3e50;
	overflow: auto;
	display: table-cell;
	box-sizing: border-box;
	vertical-align: middle;
	width: 100%;
}

body.success {
	background-color: #1abc9c;
}

.apiDocu-container, .apiDocu-container * {
	box-sizing: border-box;
}

.apiDocu-container, .apiDocu-container div {
	border-radius: 1px;
}

.apiDocu-container {
	text-align: left;
	display: inline-block;
	width: 1200px;
	height: auto;
	background-color: #ecf0f1;
	padding: 1.2em 1.5em 1.2em;
	font-family: monospace;
	vertical-align: middle;
	line-height: 1;
	margin: 0 auto 2em;
}

.apiDocu-index {
	background-color: #ecf0f1;
}

.apiDocu-container:first-child {
	margin: 2em auto;
}

@media only screen and (max-width: 1220px) {
	.apiDocu-container {
		width: 1000px;
	}
}

@media only screen and (max-width: 1020px) {
	.apiDocu-container {
		width: 800px;
	}
}

@media only screen and (max-width: 820px) {
	.apiDocu-container {
		width: 600px;
	}
}

@media only screen and (max-width: 620px) {
	.apiDocu-container {
		width: auto;
	}
}

.apiDocu-url {
	background-color: #fff;
	padding: 1em;
	font-weight: bold;
}

a.apiDocu-url {
	display: block;
	text-decoration: none;
	color: #2c3e50;
}

a.apiDocu-url:visited {
	color: #2c3e50;
}

a.apiDocu-url:hover {
	background-color: #F7F7F7;
	cursor: pointer;
}

.apiDocu-url > .apiDocu-url-method {
	font-weight: bold;
	color: #e74c3c;
	float: right;
	margin-left: 1em;
}

.apiDocu-url > .apiDocu-url-tags {
	float: right;
}

.apiDocu-url-tag {
	display: inline-block;
	color: #fff;
	padding: 6px;
	border-radius: 1px;
	font-size: 12px;
	text-transform: uppercase;
	margin-top: -0.4em;
}

.apiDocu-section-title {
	background-color: #e67e22;
	color: #fff;
	padding: 1em;
	margin-top: 1.5em;
}

.apiDocu-section-title:first-child {
	margin-top: 0.5em;
}

.apiDocu-section {
	padding: 0 0 0 1em;
}

.apiDocu-url small {
	font-weight: normal;
	display: block;
	margin-top: 1em;
	font-size: 15px;
	color: #7f8c8d;
}

.apiDocu-url-list .apiDocu-url {
	margin-top: 3px;
}

.apiDocu-url-list .apiDocu-url:first-child {
	margin-top: 0;
}

.apiDocu-url-list hr {
	border-color: transparent;
	height: 0;
	margin: 1.5em 0 0;
}

.apiDocu-mask-param {
	color: #f39c12;
}

.apiDocu-mask-param-description {
	font-weight: normal;
	margin: 0.5em 0 0 0;
	padding: 0.5em;
	background-color: #1abc9c;
	color: #fff;
}

pre.apiDocu-parameters-box {
	padding: 0.5em;
	background-color: #1abc9c;
	color: #fff;
}

.apiDocu-mask-param-description ul {
	margin: 0;
	padding: 0;
	list-style: none;
}

.apiDocu-parameters > .apiDocu-parameters-column {
	width: calc(50% - 0.5em);
	line-height: 1em;
	font-size: 15px;
	display: inline-block;
	vertical-align: top;
}

.apiDocu-description {
	background-color: #fff;
	padding: 1em;
	line-height: 1.2em;
}

.apiDocu-description small {
	font-size: 14px;
	color: #7f8c8d;
}

.apiDocu-parameters-column-content {
	background-color: #1abc9c;
	padding: 1em;
	color: #fff;
}

.apiDocu-parameters > .apiDocu-parameters-column:first-child {
	margin-right: 0.5em;
}

.apiDocu-parameters > .apiDocu-parameters-column:last-child {
	margin-left: 0.5em;
}

h1, h2, h3, h4, h5 {
	margin-top: 2em;
	font-weight: normal;
}

body.success h2 {
	margin-top: 1em;
}

h1 {
	font-size: 135%;
}

h2 {
	font-size: 120%;
}

.apiDocu-parameters-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.apiDocu-parameters-list-name {
	font-weight: bold;
}

.tracy-dump {
	background-color: transparent !important;
	margin: 0;
	padding: 0;
}

.apiDocu-parameters .apiDocu-table-with-values th {
	width: 40%;
}

table {
	max-width: 100%;
	width: 100%;
	background-color: transparent;
	border-spacing: 0 3px;
}
table th,
table td {
	padding: 0.75em;
	line-height: 20px;
	text-align: left;
	vertical-align: top;
}
table th {
	font-weight: bold;
}
table tr:nth-child(odd) td,
table tr:nth-child(odd) th {
	background-color: #fff;
}
table tr:nth-child(even) td,
table tr:nth-child(even) th {
	background-color: #fff;
}

/* Tracy dump */
pre.tracy-dump {
	text-align: left;
	color: #2980b9;
}

pre.tracy-dump div {
	padding-left: 3ex;
}

pre.tracy-dump div div {
	border-left: 1px solid rgba(0, 0, 0, .1);
	margin-left: 0.5ex;
}

.tracy-dump-array, .tracy-dump-object {
	color: #C22;
}

.tracy-dump-string {
	color: #35D;
}

.tracy-dump-number {
	color: #090;
}

.tracy-dump-null, .tracy-dump-bool {
	color: #850;
}

.tracy-dump-visibility, .tracy-dump-hash {
	font-size: 85%; color: #999;
}

.tracy-dump-indent {
	display: none;
}

pre.apiDocu-json {
	margin: 0.5em 0;
	padding: 0.5em;
	border: 1px solid #E1E1E1;
	border-radius: 1px;
	background-color: #F5F5F5;
	font-size: 13px;
	tab-size: 4;
	box-sizing: border-box;
}

.apiDocu-string {
	color: #E06A0A;
	font-weight: bold;
}

.apiDocu-comment {
	color: #939393;
}

.apiDocu-code-success {
	color: #1abc9c;
}

.apiDocu-code-warning {
	color: #e67e22;
}

.apiDocu-code-error {
	color: #e74c3c;
}
</style>
</head>
<body>

<div class="apiDocu-container apiDocu-index">
	<div class="apiDocu-url-list">

				<h2 class="apiDocu-section-title">Advocates</h2>

				<div class="apiDocu-section">
	<a href="Advocates.1.php" class="apiDocu-url">
		/api/advocate/autocomplete[/<span class="apiDocu-mask-param"&gt;</span>&lt;query&gt;</span>]

		<div class="apiDocu-url-method">GET</div>

		<div class="apiDocu-url-tags">
			
				<span style="background-color: #9b59b6;" class="apiDocu-url-tag">public</span>
		</div>
	</a>
	<a href="Advocates.2.php" class="apiDocu-url">
		/api/advocate/autocomplete

		<div class="apiDocu-url-method"></div>

	</a>
	<a href="Advocates.3.php" class="apiDocu-url">
		/api/advocate/<span class="apiDocu-mask-param"&gt;</span>&lt;id&gt;</span>

		<div class="apiDocu-url-method">GET</div>

		<div class="apiDocu-url-tags">
			
				<span style="background-color: #9b59b6;" class="apiDocu-url-tag">public</span>
		</div>
	</a>
	<a href="Advocates.4.php" class="apiDocu-url">
		/api/advocate/

		<div class="apiDocu-url-method"></div>

	</a>
	<a href="Advocates.5.php" class="apiDocu-url">
		/api/advocate-results/<span class="apiDocu-mask-param"&gt;</span>&lt;advocate&gt;</span>[/<span class="apiDocu-mask-param"&gt;</span>&lt;court&gt;</span>]

		<div class="apiDocu-url-method">GET</div>

		<div class="apiDocu-url-tags">
			
				<span style="background-color: #9b59b6;" class="apiDocu-url-tag">public</span>
		</div>
	</a>
	<a href="Advocates.6.php" class="apiDocu-url">
		/api/advocate-results/

		<div class="apiDocu-url-method"></div>

	</a>
	<a href="Advocates.7.php" class="apiDocu-url">
		/api/advocate/search[/<span class="apiDocu-mask-param"&gt;</span>&lt;query&gt;</span>/[<span class="apiDocu-mask-param"&gt;</span>&lt;start&gt;</span>-<span class="apiDocu-mask-param"&gt;</span>&lt;count&gt;</span>]]

		<div class="apiDocu-url-method">GET</div>

		<div class="apiDocu-url-tags">
			
				<span style="background-color: #9b59b6;" class="apiDocu-url-tag">public</span>
		</div>
	</a>
	<a href="Advocates.8.php" class="apiDocu-url">
		/api/advocate/search

		<div class="apiDocu-url-method"></div>

	</a>
				</div>

				<h2 class="apiDocu-section-title">Cases</h2>

				<div class="apiDocu-section">
	<a href="Cases.9.php" class="apiDocu-url">
		/api/case/<span class="apiDocu-mask-param"&gt;</span>&lt;id&gt;</span>

		<div class="apiDocu-url-method">GET</div>

		<div class="apiDocu-url-tags">
			
				<span style="background-color: #9b59b6;" class="apiDocu-url-tag">public</span>
		</div>
	</a>
	<a href="Cases.10.php" class="apiDocu-url">
		/api/case/

		<div class="apiDocu-url-method"></div>

	</a>
				</div>
	</div>
</div>



	<div class="apiDocu-container">
		<div class="apiDocu-url">
			/api/case/<span class="apiDocu-mask-param"&gt;</span>&lt;id&gt;</span>

			<div class="apiDocu-url-method">GET</div>

			<div class="apiDocu-url-tags">
				
					<span style="background-color: #9b59b6;" class="apiDocu-url-tag">public</span>
			</div>
		</div>

			<h2>Description</h2>

			<div class="apiDocu-description apiDocu-description-main">Get information about case with given ID.<br /><br><pre class="apiDocu-json">    {<br />        <span class="apiDocu-string">"id_case"</span>: 12,<br />        <span class="apiDocu-string">"id_court"</span>: 2,<br />        <span class="apiDocu-string">"registry_mark"</span>: <span class="apiDocu-string">"42 CDO 4000/2016"</span>,<br />        <span class="apiDocu-string">"tagging_advocate"</span>: {<br />            <span class="apiDocu-string">"id_advocate"</span>: 123,<br />            <span class="apiDocu-string">"fullname"</span>: <span class="apiDocu-string">"JUDr. Ing. Petr Omáčka, PhD."</span><br />        },<br />        <span class="apiDocu-string">"tagging_result"</span>: <span class="apiDocu-string">"negative"</span>,<br />        <span class="apiDocu-string">"documents"</span>: [<br />            {<br />                <span class="apiDocu-string">"id_document"</span>: 543,<br />                <span class="apiDocu-string">"mark"</span>: <span class="apiDocu-string">"ECLI:CZ:NS:2016:42.CDO.4000.2016.1"</span>,<br />                <span class="apiDocu-string">"decision_date"</span>: <span class="apiDocu-string">"2012-04-23T18:25:43.511Z"</span>,<br />                <span class="apiDocu-string">"public_link"</span>: <span class="apiDocu-string">"http:<span class="apiDocu-comment">//example.com/doc/12AS13LAA0"</span></span><br>            }<br />        ]<br />    }<br /></pre><br />Potential tagging advocate:<br /> - <b>array</b> when tagging present and valid<br /> - <b>null</b> when null, or tagging is invalid<br />Potential tagging result (@see CaseResult):<br /> - <b>negative</b><br /> - <b>neutral</b><br /> - <b>positive</b><br /> - <b>null</b> - when tagging is invalid</div>


		<h2>Methods</h2>

		<p class="apiDocu-description">GET</p>

		<div class="apiDocu-parameters">
			<h2>Mask parameters</h2>

			<table>
					<tr>
						<th>
							<span class="apiDocu-mask-param">&lt;id&gt;</span>
							<div class="apiDocu-mask-param-description">
								<ul>
									<li>
										<strong>requirement</strong>: \d+
									</li>
									<li>
										<strong>type</strong>: integer
									</li>
									<li>
										<strong>description</strong>: Case ID.
									</li>
								</ul>
							</div>
						</th>
					</tr>
			</table>
		</div>


	</div>
</body>
</html>

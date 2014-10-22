function sequentialColor(i) {
	var r, g, b;

	i %= 216;
	if(i < 0) {
		i = -i;
	}
	r = Math.floor(i / 36);
	i %= 36;
	g = Math.floor(i / 6);
	b = i % 6;
	return r * 0x330000 + g * 0x003300 + b * 0x000033;
}
function randomColor(i) {
	return sequentialColor((i<<7)%215);
}
function colorToString(color) {
	var cStr = color.toString(16);

	while(cStr.length < 6) {
		cStr = '0' + cStr;
	}
	return '#' + cStr;
}

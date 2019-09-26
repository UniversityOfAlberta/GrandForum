// Some older jquery libraries that we are using require these function, so these are for backwards compatability

$.fn.andSelf = function() {
  return this.addBack.apply(this, arguments);
}

$.fn.size = function() {
  return this.length;
}

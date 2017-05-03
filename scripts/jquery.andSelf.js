// andSelf was removed for 3.0
// Some older jquery libraries that we are using require this function, so this if for backwards compatability
$.fn.andSelf = function() {
  return this.addBack.apply(this, arguments);
}

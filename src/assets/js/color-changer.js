class ColorChanger {
  constructor() {
    this.colorClasses = [
      'page-color-brand-red',
      'page-color-brand-blue', 
      'page-color-brand-pink',
      'page-color-brand-brown',
      'page-color-brand-yellow',
      'page-color-brand-green'
    ];
    
    this.currentColorIndex = 0;
    this.intervalId = null;
    
    this.init();
  }
  
  init() {
    // Set random color on page load
    this.setRandomColor();
    
    // Start color changing interval (every 5 seconds)
    this.startColorCycle();
  }
  
  setRandomColor() {
    // Remove any existing color classes
    this.removeAllColorClasses();
    
    // Get random color class
    const randomIndex = Math.floor(Math.random() * this.colorClasses.length);
    this.currentColorIndex = randomIndex;
    
    // Apply random color class to body
    document.body.classList.add(this.colorClasses[randomIndex]);
  }
  
  nextColor() {
    // Remove current color class
    this.removeAllColorClasses();
    
    // Move to next color (cycle through)
    this.currentColorIndex = (this.currentColorIndex + 1) % this.colorClasses.length;
    
    // Apply next color class
    document.body.classList.add(this.colorClasses[this.currentColorIndex]);
  }
  
  removeAllColorClasses() {
    this.colorClasses.forEach(className => {
      document.body.classList.remove(className);
    });
  }
  
  startColorCycle() {
    // Change color every 5 seconds
    this.intervalId = setInterval(() => {
      this.nextColor();
    }, 5000);
  }
  
  stopColorCycle() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new ColorChanger();
});

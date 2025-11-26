import { useCallback, useEffect, useRef, useState } from 'react';

type UseVisualViewportOptions = {
  onKeyboardShow?: () => void;
  onKeyboardHide?: () => void;
};

const useVisualViewport = (options: UseVisualViewportOptions = {}) => {
  const [keyboardHeight, setKeyboardHeight] = useState(0);
  const [isKeyboardVisible, setIsKeyboardVisible] = useState(false);
  const initialViewportHeight = useRef<number | null>(null);

  const handleResize = useCallback(() => {
    if (!window.visualViewport) return;

    if (initialViewportHeight.current === null) {
      initialViewportHeight.current = window.visualViewport.height;
    }

    const currentHeight = window.visualViewport.height;
    const heightDiff = initialViewportHeight.current - currentHeight;

    // Consider keyboard visible if viewport shrunk by more than 100px
    const keyboardVisible = heightDiff > 100;

    setKeyboardHeight(keyboardVisible ? heightDiff : 0);

    if (keyboardVisible && !isKeyboardVisible) {
      setIsKeyboardVisible(true);
      options.onKeyboardShow?.();
    } else if (!keyboardVisible && isKeyboardVisible) {
      setIsKeyboardVisible(false);
      options.onKeyboardHide?.();
    }
  }, [isKeyboardVisible, options]);

  useEffect(() => {
    if (!window.visualViewport) return;

    // Set initial height
    initialViewportHeight.current = window.visualViewport.height;

    window.visualViewport.addEventListener('resize', handleResize);

    return () => {
      window.visualViewport?.removeEventListener('resize', handleResize);
    };
  }, [handleResize]);

  return { keyboardHeight, isKeyboardVisible };
};

export default useVisualViewport;

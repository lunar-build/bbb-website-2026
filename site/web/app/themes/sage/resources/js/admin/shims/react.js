// @dnd-kit imports 'react' directly; hand it WordPress's copy (window.React,
// behind wp-element) so there is only ever one React on the page. Aliased in
// vite.config.js.
const React = window.React;

export default React;
export const {
  Children, Component, Fragment, PureComponent, StrictMode, Suspense,
  cloneElement, createContext, createElement, createRef, forwardRef,
  isValidElement, lazy, memo, startTransition, useCallback, useContext,
  useDebugValue, useDeferredValue, useEffect, useId, useImperativeHandle,
  useInsertionEffect, useLayoutEffect, useMemo, useReducer, useRef, useState,
  useSyncExternalStore, useTransition, version,
} = React;

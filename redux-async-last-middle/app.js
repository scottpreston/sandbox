import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware } from 'redux';
import Counter from './component';
import reducer from './reducers';
import middleware from './middleware';


const store = createStore(reducer, applyMiddleware(middleware));

function delayedAction(type) {
  const requestId = new Date().getTime();
  store.dispatch({ type: "START_REQUEST", requestId : requestId});
  setTimeout(() => store.dispatch({ type: type , requestId : requestId}), 1000);
}

const render = () => ReactDOM.render( // turns ReactDOM.render into executable function
  <Counter
    value={store.getState().count}
    onIncrement={() => delayedAction('INCREMENT')}
    onDecrement={() => delayedAction('DECREMENT')}
  />,
  document.getElementById('root')
);

render();
store.subscribe(render);

import{$ as e,A as t,F as n,H as r,I as i,M as a,N as o,P as s,U as c,W as l,at as u,c as d,ct as f,g as p,h as ee,l as m,o as h,ot as g,r as _,rt as v,s as y,u as b,w as x}from"./runtime-core.esm-bundler-4I4cuEBR.js";import{t as S}from"./classnames-CRHlWn3X.js";import{a as C,i as w,o as T,r as E,s as D,t as O}from"./ripple-DPHnVqbs.js";import{bt as k,ct as A,dt as te,et as j,i as M,k as ne,mt as re,n as N,ot as P,t as ie,ut as ae}from"./app-D_eFA3wB.js";import{t as F}from"./basecomponent-CK-11Ajl.js";import{t as oe}from"./theme-CdpjvIu9.js";var I=`
    .p-toast {
        width: dt('toast.width');
        white-space: pre-line;
        word-break: break-word;
    }

    .p-toast-message {
        margin: 0 0 1rem 0;
        display: grid;
        grid-template-rows: 1fr;
    }

    .p-toast-message-icon {
        flex-shrink: 0;
        font-size: dt('toast.icon.size');
        width: dt('toast.icon.size');
        height: dt('toast.icon.size');
    }

    .p-toast-message-content {
        display: flex;
        align-items: flex-start;
        padding: dt('toast.content.padding');
        gap: dt('toast.content.gap');
        min-height: 0;
        overflow: hidden;
        transition: padding 250ms ease-in;
    }

    .p-toast-message-text {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: dt('toast.text.gap');
    }

    .p-toast-summary {
        font-weight: dt('toast.summary.font.weight');
        font-size: dt('toast.summary.font.size');
    }

    .p-toast-detail {
        font-weight: dt('toast.detail.font.weight');
        font-size: dt('toast.detail.font.size');
    }

    .p-toast-close-button {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        background: transparent;
        transition:
            background dt('toast.transition.duration'),
            color dt('toast.transition.duration'),
            outline-color dt('toast.transition.duration'),
            box-shadow dt('toast.transition.duration');
        outline-color: transparent;
        color: inherit;
        width: dt('toast.close.button.width');
        height: dt('toast.close.button.height');
        border-radius: dt('toast.close.button.border.radius');
        margin: -25% 0 0 0;
        right: -25%;
        padding: 0;
        border: none;
        user-select: none;
    }

    .p-toast-close-button:dir(rtl) {
        margin: -25% 0 0 auto;
        left: -25%;
        right: auto;
    }

    .p-toast-message-info,
    .p-toast-message-success,
    .p-toast-message-warn,
    .p-toast-message-error,
    .p-toast-message-secondary,
    .p-toast-message-contrast {
        border-width: dt('toast.border.width');
        border-style: solid;
        backdrop-filter: blur(dt('toast.blur'));
        border-radius: dt('toast.border.radius');
    }

    .p-toast-close-icon {
        font-size: dt('toast.close.icon.size');
        width: dt('toast.close.icon.size');
        height: dt('toast.close.icon.size');
    }

    .p-toast-close-button:focus-visible {
        outline-width: dt('focus.ring.width');
        outline-style: dt('focus.ring.style');
        outline-offset: dt('focus.ring.offset');
    }

    .p-toast-message-info {
        background: dt('toast.info.background');
        border-color: dt('toast.info.border.color');
        color: dt('toast.info.color');
        box-shadow: dt('toast.info.shadow');
    }

    .p-toast-message-info .p-toast-detail {
        color: dt('toast.info.detail.color');
    }

    .p-toast-message-info .p-toast-close-button:focus-visible {
        outline-color: dt('toast.info.close.button.focus.ring.color');
        box-shadow: dt('toast.info.close.button.focus.ring.shadow');
    }

    .p-toast-message-info .p-toast-close-button:hover {
        background: dt('toast.info.close.button.hover.background');
    }

    .p-toast-message-success {
        background: dt('toast.success.background');
        border-color: dt('toast.success.border.color');
        color: dt('toast.success.color');
        box-shadow: dt('toast.success.shadow');
    }

    .p-toast-message-success .p-toast-detail {
        color: dt('toast.success.detail.color');
    }

    .p-toast-message-success .p-toast-close-button:focus-visible {
        outline-color: dt('toast.success.close.button.focus.ring.color');
        box-shadow: dt('toast.success.close.button.focus.ring.shadow');
    }

    .p-toast-message-success .p-toast-close-button:hover {
        background: dt('toast.success.close.button.hover.background');
    }

    .p-toast-message-warn {
        background: dt('toast.warn.background');
        border-color: dt('toast.warn.border.color');
        color: dt('toast.warn.color');
        box-shadow: dt('toast.warn.shadow');
    }

    .p-toast-message-warn .p-toast-detail {
        color: dt('toast.warn.detail.color');
    }

    .p-toast-message-warn .p-toast-close-button:focus-visible {
        outline-color: dt('toast.warn.close.button.focus.ring.color');
        box-shadow: dt('toast.warn.close.button.focus.ring.shadow');
    }

    .p-toast-message-warn .p-toast-close-button:hover {
        background: dt('toast.warn.close.button.hover.background');
    }

    .p-toast-message-error {
        background: dt('toast.error.background');
        border-color: dt('toast.error.border.color');
        color: dt('toast.error.color');
        box-shadow: dt('toast.error.shadow');
    }

    .p-toast-message-error .p-toast-detail {
        color: dt('toast.error.detail.color');
    }

    .p-toast-message-error .p-toast-close-button:focus-visible {
        outline-color: dt('toast.error.close.button.focus.ring.color');
        box-shadow: dt('toast.error.close.button.focus.ring.shadow');
    }

    .p-toast-message-error .p-toast-close-button:hover {
        background: dt('toast.error.close.button.hover.background');
    }

    .p-toast-message-secondary {
        background: dt('toast.secondary.background');
        border-color: dt('toast.secondary.border.color');
        color: dt('toast.secondary.color');
        box-shadow: dt('toast.secondary.shadow');
    }

    .p-toast-message-secondary .p-toast-detail {
        color: dt('toast.secondary.detail.color');
    }

    .p-toast-message-secondary .p-toast-close-button:focus-visible {
        outline-color: dt('toast.secondary.close.button.focus.ring.color');
        box-shadow: dt('toast.secondary.close.button.focus.ring.shadow');
    }

    .p-toast-message-secondary .p-toast-close-button:hover {
        background: dt('toast.secondary.close.button.hover.background');
    }

    .p-toast-message-contrast {
        background: dt('toast.contrast.background');
        border-color: dt('toast.contrast.border.color');
        color: dt('toast.contrast.color');
        box-shadow: dt('toast.contrast.shadow');
    }
    
    .p-toast-message-contrast .p-toast-detail {
        color: dt('toast.contrast.detail.color');
    }

    .p-toast-message-contrast .p-toast-close-button:focus-visible {
        outline-color: dt('toast.contrast.close.button.focus.ring.color');
        box-shadow: dt('toast.contrast.close.button.focus.ring.shadow');
    }

    .p-toast-message-contrast .p-toast-close-button:hover {
        background: dt('toast.contrast.close.button.hover.background');
    }

    .p-toast-top-center {
        transform: translateX(-50%);
    }

    .p-toast-bottom-center {
        transform: translateX(-50%);
    }

    .p-toast-center {
        min-width: 20vw;
        transform: translate(-50%, -50%);
    }

    .p-toast-message-enter-active {
        animation: p-animate-toast-enter 300ms ease-out;
    }

    .p-toast-message-leave-active {
        animation: p-animate-toast-leave 250ms ease-in;
    }

    .p-toast-message-leave-to .p-toast-message-content {
        padding-top: 0;
        padding-bottom: 0;
    }

    @keyframes p-animate-toast-enter {
        from {
            opacity: 0;
            transform: scale(0.6);
        }
        to {
            opacity: 1;
            grid-template-rows: 1fr;
        }
    }

     @keyframes p-animate-toast-leave {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
            margin-bottom: 0;
            grid-template-rows: 0fr;
            transform: translateY(-100%) scale(0.6);
        }
    }
`;function L(e){"@babel/helpers - typeof";return L=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},L(e)}function R(e,t,n){return(t=se(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function se(e){var t=ce(e,`string`);return L(t)==`symbol`?t:t+``}function ce(e,t){if(L(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(L(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var le=M.extend({name:`toast`,style:I,classes:{root:function(e){return[`p-toast p-component p-toast-`+e.props.position]},message:function(e){var t=e.props;return[`p-toast-message`,{"p-toast-message-info":t.message.severity===`info`||t.message.severity===void 0,"p-toast-message-warn":t.message.severity===`warn`,"p-toast-message-error":t.message.severity===`error`,"p-toast-message-success":t.message.severity===`success`,"p-toast-message-secondary":t.message.severity===`secondary`,"p-toast-message-contrast":t.message.severity===`contrast`}]},messageContent:`p-toast-message-content`,messageIcon:function(e){var t=e.props;return[`p-toast-message-icon`,R(R(R(R({},t.infoIcon,t.message.severity===`info`),t.warnIcon,t.message.severity===`warn`),t.errorIcon,t.message.severity===`error`),t.successIcon,t.message.severity===`success`)]},messageText:`p-toast-message-text`,summary:`p-toast-summary`,detail:`p-toast-detail`,closeButton:`p-toast-close-button`,closeIcon:`p-toast-close-icon`},inlineStyles:{root:function(e){var t=e.position;return{position:`fixed`,top:t===`top-right`||t===`top-left`||t===`top-center`?`20px`:t===`center`?`50%`:null,right:(t===`top-right`||t===`bottom-right`)&&`20px`,bottom:(t===`bottom-left`||t===`bottom-right`||t===`bottom-center`)&&`20px`,left:t===`top-left`||t===`bottom-left`?`20px`:t===`center`||t===`top-center`||t===`bottom-center`?`50%`:null}}}}),z={name:`ExclamationTriangleIcon`,extends:C};function ue(e){return me(e)||pe(e)||fe(e)||de()}function de(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function fe(e,t){if(e){if(typeof e==`string`)return B(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?B(e,t):void 0}}function pe(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function me(e){if(Array.isArray(e))return B(e)}function B(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function he(e,n,r,i,a,o){return t(),b(`svg`,x({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),ue(n[0]||=[y(`path`,{d:`M13.4018 13.1893H0.598161C0.49329 13.189 0.390283 13.1615 0.299143 13.1097C0.208003 13.0578 0.131826 12.9832 0.0780112 12.8932C0.0268539 12.8015 0 12.6982 0 12.5931C0 12.4881 0.0268539 12.3848 0.0780112 12.293L6.47985 1.08982C6.53679 1.00399 6.61408 0.933574 6.70484 0.884867C6.7956 0.836159 6.897 0.810669 7 0.810669C7.103 0.810669 7.2044 0.836159 7.29516 0.884867C7.38592 0.933574 7.46321 1.00399 7.52015 1.08982L13.922 12.293C13.9731 12.3848 14 12.4881 14 12.5931C14 12.6982 13.9731 12.8015 13.922 12.8932C13.8682 12.9832 13.792 13.0578 13.7009 13.1097C13.6097 13.1615 13.5067 13.189 13.4018 13.1893ZM1.63046 11.989H12.3695L7 2.59425L1.63046 11.989Z`,fill:`currentColor`},null,-1),y(`path`,{d:`M6.99996 8.78801C6.84143 8.78594 6.68997 8.72204 6.57787 8.60993C6.46576 8.49782 6.40186 8.34637 6.39979 8.18784V5.38703C6.39979 5.22786 6.46302 5.0752 6.57557 4.96265C6.68813 4.85009 6.84078 4.78686 6.99996 4.78686C7.15914 4.78686 7.31179 4.85009 7.42435 4.96265C7.5369 5.0752 7.60013 5.22786 7.60013 5.38703V8.18784C7.59806 8.34637 7.53416 8.49782 7.42205 8.60993C7.30995 8.72204 7.15849 8.78594 6.99996 8.78801Z`,fill:`currentColor`},null,-1),y(`path`,{d:`M6.99996 11.1887C6.84143 11.1866 6.68997 11.1227 6.57787 11.0106C6.46576 10.8985 6.40186 10.7471 6.39979 10.5885V10.1884C6.39979 10.0292 6.46302 9.87658 6.57557 9.76403C6.68813 9.65147 6.84078 9.58824 6.99996 9.58824C7.15914 9.58824 7.31179 9.65147 7.42435 9.76403C7.5369 9.87658 7.60013 10.0292 7.60013 10.1884V10.5885C7.59806 10.7471 7.53416 10.8985 7.42205 11.0106C7.30995 11.1227 7.15849 11.1866 6.99996 11.1887Z`,fill:`currentColor`},null,-1)]),16)}z.render=he;var V={name:`InfoCircleIcon`,extends:C};function ge(e){return be(e)||ye(e)||ve(e)||_e()}function _e(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function ve(e,t){if(e){if(typeof e==`string`)return H(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?H(e,t):void 0}}function ye(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function be(e){if(Array.isArray(e))return H(e)}function H(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function xe(e,n,r,i,a,o){return t(),b(`svg`,x({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),ge(n[0]||=[y(`path`,{"fill-rule":`evenodd`,"clip-rule":`evenodd`,d:`M3.11101 12.8203C4.26215 13.5895 5.61553 14 7 14C8.85652 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85652 14 7C14 5.61553 13.5895 4.26215 12.8203 3.11101C12.0511 1.95987 10.9579 1.06266 9.67879 0.532846C8.3997 0.00303296 6.99224 -0.13559 5.63437 0.134506C4.2765 0.404603 3.02922 1.07129 2.05026 2.05026C1.07129 3.02922 0.404603 4.2765 0.134506 5.63437C-0.13559 6.99224 0.00303296 8.3997 0.532846 9.67879C1.06266 10.9579 1.95987 12.0511 3.11101 12.8203ZM3.75918 2.14976C4.71846 1.50879 5.84628 1.16667 7 1.16667C8.5471 1.16667 10.0308 1.78125 11.1248 2.87521C12.2188 3.96918 12.8333 5.45291 12.8333 7C12.8333 8.15373 12.4912 9.28154 11.8502 10.2408C11.2093 11.2001 10.2982 11.9478 9.23232 12.3893C8.16642 12.8308 6.99353 12.9463 5.86198 12.7212C4.73042 12.4962 3.69102 11.9406 2.87521 11.1248C2.05941 10.309 1.50384 9.26958 1.27876 8.13803C1.05367 7.00647 1.16919 5.83358 1.61071 4.76768C2.05222 3.70178 2.79989 2.79074 3.75918 2.14976ZM7.00002 4.8611C6.84594 4.85908 6.69873 4.79698 6.58977 4.68801C6.48081 4.57905 6.4187 4.43185 6.41669 4.27776V3.88888C6.41669 3.73417 6.47815 3.58579 6.58754 3.4764C6.69694 3.367 6.84531 3.30554 7.00002 3.30554C7.15473 3.30554 7.3031 3.367 7.4125 3.4764C7.52189 3.58579 7.58335 3.73417 7.58335 3.88888V4.27776C7.58134 4.43185 7.51923 4.57905 7.41027 4.68801C7.30131 4.79698 7.1541 4.85908 7.00002 4.8611ZM7.00002 10.6945C6.84594 10.6925 6.69873 10.6304 6.58977 10.5214C6.48081 10.4124 6.4187 10.2652 6.41669 10.1111V6.22225C6.41669 6.06754 6.47815 5.91917 6.58754 5.80977C6.69694 5.70037 6.84531 5.63892 7.00002 5.63892C7.15473 5.63892 7.3031 5.70037 7.4125 5.80977C7.52189 5.91917 7.58335 6.06754 7.58335 6.22225V10.1111C7.58134 10.2652 7.51923 10.4124 7.41027 10.5214C7.30131 10.6304 7.1541 10.6925 7.00002 10.6945Z`,fill:`currentColor`},null,-1)]),16)}V.render=xe;var U={name:`TimesCircleIcon`,extends:C};function Se(e){return Ee(e)||Te(e)||we(e)||Ce()}function Ce(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function we(e,t){if(e){if(typeof e==`string`)return W(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?W(e,t):void 0}}function Te(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Ee(e){if(Array.isArray(e))return W(e)}function W(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function De(e,n,r,i,a,o){return t(),b(`svg`,x({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),Se(n[0]||=[y(`path`,{"fill-rule":`evenodd`,"clip-rule":`evenodd`,d:`M7 14C5.61553 14 4.26215 13.5895 3.11101 12.8203C1.95987 12.0511 1.06266 10.9579 0.532846 9.67879C0.00303296 8.3997 -0.13559 6.99224 0.134506 5.63437C0.404603 4.2765 1.07129 3.02922 2.05026 2.05026C3.02922 1.07129 4.2765 0.404603 5.63437 0.134506C6.99224 -0.13559 8.3997 0.00303296 9.67879 0.532846C10.9579 1.06266 12.0511 1.95987 12.8203 3.11101C13.5895 4.26215 14 5.61553 14 7C14 8.85652 13.2625 10.637 11.9497 11.9497C10.637 13.2625 8.85652 14 7 14ZM7 1.16667C5.84628 1.16667 4.71846 1.50879 3.75918 2.14976C2.79989 2.79074 2.05222 3.70178 1.61071 4.76768C1.16919 5.83358 1.05367 7.00647 1.27876 8.13803C1.50384 9.26958 2.05941 10.309 2.87521 11.1248C3.69102 11.9406 4.73042 12.4962 5.86198 12.7212C6.99353 12.9463 8.16642 12.8308 9.23232 12.3893C10.2982 11.9478 11.2093 11.2001 11.8502 10.2408C12.4912 9.28154 12.8333 8.15373 12.8333 7C12.8333 5.45291 12.2188 3.96918 11.1248 2.87521C10.0308 1.78125 8.5471 1.16667 7 1.16667ZM4.66662 9.91668C4.58998 9.91704 4.51404 9.90209 4.44325 9.87271C4.37246 9.84333 4.30826 9.8001 4.2544 9.74557C4.14516 9.6362 4.0838 9.48793 4.0838 9.33335C4.0838 9.17876 4.14516 9.0305 4.2544 8.92113L6.17553 7L4.25443 5.07891C4.15139 4.96832 4.09529 4.82207 4.09796 4.67094C4.10063 4.51982 4.16185 4.37563 4.26872 4.26876C4.3756 4.16188 4.51979 4.10066 4.67091 4.09799C4.82204 4.09532 4.96829 4.15142 5.07887 4.25446L6.99997 6.17556L8.92106 4.25446C9.03164 4.15142 9.1779 4.09532 9.32903 4.09799C9.48015 4.10066 9.62434 4.16188 9.73121 4.26876C9.83809 4.37563 9.89931 4.51982 9.90198 4.67094C9.90464 4.82207 9.84855 4.96832 9.74551 5.07891L7.82441 7L9.74554 8.92113C9.85478 9.0305 9.91614 9.17876 9.91614 9.33335C9.91614 9.48793 9.85478 9.6362 9.74554 9.74557C9.69168 9.8001 9.62748 9.84333 9.55669 9.87271C9.4859 9.90209 9.40996 9.91704 9.33332 9.91668C9.25668 9.91704 9.18073 9.90209 9.10995 9.87271C9.03916 9.84333 8.97495 9.8001 8.9211 9.74557L6.99997 7.82444L5.07884 9.74557C5.02499 9.8001 4.96078 9.84333 4.88999 9.87271C4.81921 9.90209 4.74326 9.91704 4.66662 9.91668Z`,fill:`currentColor`},null,-1)]),16)}U.render=De;var Oe={name:`BaseToast`,extends:F,props:{group:{type:String,default:null},position:{type:String,default:`top-right`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},breakpoints:{type:Object,default:null},closeIcon:{type:String,default:void 0},infoIcon:{type:String,default:void 0},warnIcon:{type:String,default:void 0},errorIcon:{type:String,default:void 0},successIcon:{type:String,default:void 0},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},style:le,provide:function(){return{$pcToast:this,$parentInstance:this}}};function G(e){"@babel/helpers - typeof";return G=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},G(e)}function ke(e,t,n){return(t=Ae(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ae(e){var t=je(e,`string`);return G(t)==`symbol`?t:t+``}function je(e,t){if(G(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(G(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var K={name:`ToastMessage`,hostName:`Toast`,extends:F,emits:[`close`],closeTimeout:null,createdAt:null,lifeRemaining:null,props:{message:{type:null,default:null},templates:{type:Object,default:null},closeIcon:{type:String,default:null},infoIcon:{type:String,default:null},warnIcon:{type:String,default:null},errorIcon:{type:String,default:null},successIcon:{type:String,default:null},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},mounted:function(){this.message.life&&(this.lifeRemaining=this.message.life,this.startTimeout())},beforeUnmount:function(){this.clearCloseTimeout()},methods:{startTimeout:function(){var e=this;this.createdAt=new Date().valueOf(),this.closeTimeout=setTimeout(function(){e.close({message:e.message,type:`life-end`})},this.lifeRemaining)},close:function(e){this.$emit(`close`,e)},onCloseClick:function(){this.clearCloseTimeout(),this.close({message:this.message,type:`close`})},clearCloseTimeout:function(){this.closeTimeout&&=(clearTimeout(this.closeTimeout),null)},onMessageClick:function(e){var t;(t=this.onClick)==null||t.call(this,{originalEvent:e,message:this.message})},handleMouseEnter:function(e){if(this.onMouseEnter){if(this.onMouseEnter({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&(this.lifeRemaining=this.createdAt+this.lifeRemaining-new Date().valueOf(),this.createdAt=null,this.clearCloseTimeout())}},handleMouseLeave:function(e){if(this.onMouseLeave){if(this.onMouseLeave({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&this.startTimeout()}}},computed:{iconComponent:function(){return{info:!this.infoIcon&&V,success:!this.successIcon&&w,warn:!this.warnIcon&&z,error:!this.errorIcon&&U}[this.message.severity]},closeAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.close:void 0},dataP:function(){return S(ke({},this.message.severity,this.message.severity))}},components:{TimesIcon:E,InfoCircleIcon:V,CheckIcon:w,ExclamationTriangleIcon:z,TimesCircleIcon:U},directives:{ripple:O}};function q(e){"@babel/helpers - typeof";return q=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},q(e)}function J(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function Y(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?J(Object(n),!0).forEach(function(t){Me(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):J(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function Me(e,t,n){return(t=Ne(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ne(e){var t=Pe(e,`string`);return q(t)==`symbol`?t:t+``}function Pe(e,t){if(q(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(q(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var Fe=[`data-p`],Ie=[`data-p`],Le=[`data-p`],Re=[`data-p`],ze=[`aria-label`,`data-p`];function Be(e,r,a,o,s,c){var u=n(`ripple`);return t(),b(`div`,x({class:[e.cx(`message`),a.message.styleClass],role:`alert`,"aria-live":`assertive`,"aria-atomic":`true`,"data-p":c.dataP},e.ptm(`message`),{onClick:r[1]||=function(){return c.onMessageClick&&c.onMessageClick.apply(c,arguments)},onMouseenter:r[2]||=function(){return c.handleMouseEnter&&c.handleMouseEnter.apply(c,arguments)},onMouseleave:r[3]||=function(){return c.handleMouseLeave&&c.handleMouseLeave.apply(c,arguments)}}),[a.templates.container?(t(),d(i(a.templates.container),{key:0,message:a.message,closeCallback:c.onCloseClick},null,8,[`message`,`closeCallback`])):(t(),b(`div`,x({key:1,class:[e.cx(`messageContent`),a.message.contentStyleClass]},e.ptm(`messageContent`)),[a.templates.message?(t(),d(i(a.templates.message),{key:1,message:a.message},null,8,[`message`])):(t(),b(_,{key:0},[(t(),d(i(a.templates.messageicon?a.templates.messageicon:a.templates.icon?a.templates.icon:c.iconComponent&&c.iconComponent.name?c.iconComponent:`span`),x({class:e.cx(`messageIcon`)},e.ptm(`messageIcon`)),null,16,[`class`])),y(`div`,x({class:e.cx(`messageText`),"data-p":c.dataP},e.ptm(`messageText`)),[y(`span`,x({class:e.cx(`summary`),"data-p":c.dataP},e.ptm(`summary`)),f(a.message.summary),17,Le),a.message.detail?(t(),b(`div`,x({key:0,class:e.cx(`detail`),"data-p":c.dataP},e.ptm(`detail`)),f(a.message.detail),17,Re)):m(``,!0)],16,Ie)],64)),a.message.closable===!1?m(``,!0):(t(),b(`div`,g(x({key:2},e.ptm(`buttonContainer`))),[l((t(),b(`button`,x({class:e.cx(`closeButton`),type:`button`,"aria-label":c.closeAriaLabel,onClick:r[0]||=function(){return c.onCloseClick&&c.onCloseClick.apply(c,arguments)},autofocus:``,"data-p":c.dataP},Y(Y({},a.closeButtonProps),e.ptm(`closeButton`))),[(t(),d(i(a.templates.closeicon||`TimesIcon`),x({class:[e.cx(`closeIcon`),a.closeIcon]},e.ptm(`closeIcon`)),null,16,[`class`]))],16,ze)),[[u]])],16))],16))],16,Fe)}K.render=Be;function X(e){"@babel/helpers - typeof";return X=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},X(e)}function Ve(e,t,n){return(t=He(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function He(e){var t=Ue(e,`string`);return X(t)==`symbol`?t:t+``}function Ue(e,t){if(X(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(X(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}function We(e){return Je(e)||qe(e)||Ke(e)||Ge()}function Ge(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Ke(e,t){if(e){if(typeof e==`string`)return Z(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Z(e,t):void 0}}function qe(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Je(e){if(Array.isArray(e))return Z(e)}function Z(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var Ye=0,Q={name:`Toast`,extends:Oe,inheritAttrs:!1,emits:[`close`,`life-end`],data:function(){return{messages:[]}},styleElement:null,mounted:function(){N.on(`add`,this.onAdd),N.on(`remove`,this.onRemove),N.on(`remove-group`,this.onRemoveGroup),N.on(`remove-all-groups`,this.onRemoveAllGroups),this.breakpoints&&this.createStyle()},beforeUnmount:function(){this.destroyStyle(),this.$refs.container&&this.autoZIndex&&D.clear(this.$refs.container),N.off(`add`,this.onAdd),N.off(`remove`,this.onRemove),N.off(`remove-group`,this.onRemoveGroup),N.off(`remove-all-groups`,this.onRemoveAllGroups)},methods:{add:function(e){e.id??=Ye++,this.messages=[].concat(We(this.messages),[e])},remove:function(e){var t=this.messages.findIndex(function(t){return t.id===e.message.id});t!==-1&&(this.messages.splice(t,1),this.$emit(e.type,{message:e.message}))},onAdd:function(e){this.group==e.group&&this.add(e)},onRemove:function(e){this.remove({message:e,type:`close`})},onRemoveGroup:function(e){this.group===e&&(this.messages=[])},onRemoveAllGroups:function(){var e=this;this.messages.forEach(function(t){return e.$emit(`close`,{message:t})}),this.messages=[]},onEnter:function(){this.autoZIndex&&D.set(`modal`,this.$refs.container,this.baseZIndex||this.$primevue.config.zIndex.modal)},onLeave:function(){var e=this;this.$refs.container&&this.autoZIndex&&j(this.messages)&&setTimeout(function(){D.clear(e.$refs.container)},200)},createStyle:function(){if(!this.styleElement&&!this.isUnstyled){var e;this.styleElement=document.createElement(`style`),this.styleElement.type=`text/css`,ne(this.styleElement,`nonce`,(e=this.$primevue)==null||(e=e.config)==null||(e=e.csp)==null?void 0:e.nonce),document.head.appendChild(this.styleElement);var t=``;for(var n in this.breakpoints){var r=``;for(var i in this.breakpoints[n])r+=i+`:`+this.breakpoints[n][i]+`!important;`;t+=`
                        @media screen and (max-width: ${n}) {
                            .p-toast[${this.$attrSelector}] {
                                ${r}
                            }
                        }
                    `}this.styleElement.innerHTML=t}},destroyStyle:function(){this.styleElement&&=(document.head.removeChild(this.styleElement),null)}},computed:{dataP:function(){return S(Ve({},this.position,this.position))}},components:{ToastMessage:K,Portal:T}};function $(e){"@babel/helpers - typeof";return $=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},$(e)}function Xe(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function Ze(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?Xe(Object(n),!0).forEach(function(t){Qe(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):Xe(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function Qe(e,t,n){return(t=$e(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function $e(e){var t=et(e,`string`);return $(t)==`symbol`?t:t+``}function et(e,t){if($(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if($(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var tt=[`data-p`];function nt(e,n,r,i,o,l){var u=s(`ToastMessage`),f=s(`Portal`);return t(),d(f,null,{default:c(function(){return[y(`div`,x({ref:`container`,class:e.cx(`root`),style:e.sx(`root`,!0,{position:e.position}),"data-p":l.dataP},e.ptmi(`root`)),[p(re,x({name:`p-toast-message`,tag:`div`,onEnter:l.onEnter,onLeave:l.onLeave},Ze({},e.ptm(`transition`))),{default:c(function(){return[(t(!0),b(_,null,a(o.messages,function(r){return t(),d(u,{key:r.id,message:r,templates:e.$slots,closeIcon:e.closeIcon,infoIcon:e.infoIcon,warnIcon:e.warnIcon,errorIcon:e.errorIcon,successIcon:e.successIcon,closeButtonProps:e.closeButtonProps,onMouseEnter:e.onMouseEnter,onMouseLeave:e.onMouseLeave,onClick:e.onClick,unstyled:e.unstyled,onClose:n[0]||=function(e){return l.remove(e)},pt:e.pt},null,8,[`message`,`templates`,`closeIcon`,`infoIcon`,`warnIcon`,`errorIcon`,`successIcon`,`closeButtonProps`,`onMouseEnter`,`onMouseLeave`,`onClick`,`unstyled`,`pt`])}),128))]}),_:1},16,[`onEnter`,`onLeave`])],16,tt)]}),_:1})}Q.render=nt;var rt=P(`admin`,()=>{let t=e(!0),n=e(`this_month`);function r(){t.value=!t.value}function i(e){n.value=e}return{isSidebarOpen:t,activeRange:n,toggleSidebar:r,setRange:i}}),it={class:`min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 flex transition-colors duration-300`},at={class:`h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-800`},ot=[`src`],st={key:1,class:`w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0`},ct={class:`flex flex-col min-w-0`},lt={class:`font-bold text-sm text-gray-900 dark:text-white leading-tight truncate`},ut={class:`flex-1 py-4 px-3 space-y-4 overflow-y-auto`},dt={class:`border-gray-100 dark:border-gray-800 my-2`},ft={class:`w-4 h-4 flex-shrink-0`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},pt=[`d`],mt={key:0,class:`p-4 border-t border-gray-200 dark:border-gray-800`},ht={class:`truncate`},gt={class:`p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/20 dark:bg-gray-900/10`},_t={class:`flex flex-wrap items-center gap-3`},vt=[`src`,`alt`],yt={key:1,class:`text-[9px] font-bold text-gray-500 dark:text-gray-400 group-hover:text-amber-500 transition-colors`},bt={class:`flex-1 flex flex-col min-w-0 overflow-hidden`},xt={class:`h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 z-10 sticky top-0`},St={class:`hidden lg:flex items-center gap-3 min-w-0`},Ct={key:0,class:`text-xs text-gray-500 dark:text-gray-400 font-medium leading-none truncate`},wt={class:`lg:hidden flex items-center gap-2 min-w-0`},Tt=[`src`],Et={key:1,class:`w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0`},Dt={class:`flex flex-col min-w-0`},Ot={class:`font-bold text-sm text-gray-900 dark:text-white leading-tight truncate`},kt={class:`flex items-center gap-4`},At={key:0,class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},jt={key:1,class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},Mt={class:`flex items-center gap-3 border-l border-gray-200 dark:border-gray-800 pl-4`},Nt={class:`text-right hidden sm:block`},Pt={class:`text-xs font-semibold text-gray-900 dark:text-white`},Ft={class:`text-[10px] text-gray-500 dark:text-gray-400`},It={class:`flex-1 overflow-y-auto p-6 pb-24 lg:pb-6 flex flex-col justify-between`},Lt={key:0,class:`mt-12 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4`},Rt={class:`flex flex-wrap items-center gap-6`},zt=[`src`,`alt`],Bt={class:`text-[10px] font-semibold text-gray-500 dark:text-gray-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors`},Vt={class:`lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 z-50 px-2 py-1 shadow-lg`},Ht={class:`flex justify-around items-center`},Ut={class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},Wt=[`d`],Gt={class:`text-[10px]`},Kt={class:`lg:hidden fixed inset-0 z-[100] flex`},qt={class:`relative flex flex-col w-4/5 max-w-xs h-full bg-white dark:bg-gray-900 border-r border-gray-250 dark:border-gray-800 shadow-2xl p-5 z-10 transition-transform duration-300`},Jt={class:`flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-800 mb-4`},Yt={class:`flex-1 overflow-y-auto space-y-4 pr-1`},Xt={class:`text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1`},Zt={class:`w-4 h-4 flex-shrink-0`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},Qt=[`d`],$t={class:`truncate`},en={__name:`AdminLayout`,setup(n){let s=ae(),g=ie(),S=s.props.auth?.user||{},C=s.props.siteConfig||{},w=s.props.menus||[];r(()=>s.props.flash,e=>{e&&(e.success&&g.add({severity:`success`,summary:`Berhasil`,detail:e.success,life:5e3}),e.error&&g.add({severity:`error`,summary:`Error`,detail:e.error,life:7e3}),e.info&&g.add({severity:`info`,summary:`Info`,detail:e.info,life:7e3}),e.warning&&g.add({severity:`warn`,summary:`Perhatian`,detail:e.warning,life:6e3}))},{immediate:!0,deep:!0});let T=h(()=>{if(!s.props.auth?.roles)return null;let e=s.props.auth.roles;return e.includes(`customer`)?`customer.dashboard`:e.includes(`reseller`)?`reseller.dashboard`:e.includes(`pengrajin`)?`artisan.dashboard`:e.some(e=>[`super-admin`,`owner`,`operator`].includes(e))?`dashboard`:null}),E=e(typeof window<`u`?!!window.isAppInstallable:!1);typeof window<`u`&&window.addEventListener(`pwa-installable-status`,e=>{E.value=e.detail});let D=async()=>{let e=typeof window<`u`?window.deferredInstallPrompt:null;if(!e)return;e.prompt();let{outcome:t}=await e.userChoice;t===`accepted`&&(typeof window<`u`&&(window.deferredInstallPrompt=null,window.isAppInstallable=!1),E.value=!1)},O=oe(),j=rt(),M=e(!1),ne=()=>{te.post(route(`logout`))},re=h(()=>s.props.auth?.permissions||[]),N=h(()=>s.props.auth?.roles||[]),P=e=>re.value.includes(e),F=e=>!!(route().current(e)||route().current(`my-orders.show`)&&(e===`customer.orders`&&N.value.includes(`customer`)||e===`reseller.orders`&&N.value.includes(`reseller`))),I=h(()=>{let e=[],t=N.value.includes(`customer`),n=N.value.includes(`reseller`),r=N.value.includes(`pengrajin`);if(t)return[{title:`Portal Pelanggan`,items:[{name:`Portal Dashboard`,route:`customer.dashboard`,icon:`M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21h6`},{name:`Belanja Sekarang`,route:`catalog.index`,icon:`M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z`},{name:`Pesanan Saya`,route:`customer.orders`,icon:`M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2`},{name:`Profil Saya`,route:`customer.profile`,icon:`M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z`}]}];if(n)return[{title:`Portal Reseller`,items:[{name:`Portal Reseller`,route:`reseller.dashboard`,icon:`M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21h6`},{name:`Belanja Sekarang`,route:`catalog.index`,icon:`M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z`},{name:`Riwayat Order`,route:`reseller.orders`,icon:`M9 5H7a2 2 0 00-2-2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2`},{name:`Tagihan / Billing`,route:`reseller.billing`,icon:`M9 8h6m-6 2h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z`},{name:`Profil Reseller`,route:`reseller.profile`,icon:`M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z`}]}];if(r)return[{title:`Portal Pengrajin`,items:[{name:`Upah & Pekerjaan`,route:`artisan.dashboard`,icon:`M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z`},{name:`Katalog`,route:`catalog.index`,icon:`M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z`}]}];for(let t of w){let n=t.items.filter(e=>!e.permission||P(e.permission));n.length>0&&e.push({title:t.title,items:n})}return e}),L=h(()=>{let e=[];return I.value.forEach(t=>{t.items.forEach(t=>{e.push(t)})}),e}),R=h(()=>{let e=N.value.includes(`customer`),t=N.value.includes(`reseller`),n=N.value.includes(`pengrajin`);if(e)return[{name:`Dashboard`,route:`customer.dashboard`,icon:`M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21h6`},{name:`Belanja`,route:`catalog.index`,icon:`M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z`},{name:`Pesanan`,route:`customer.orders`,icon:`M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2`},{name:`Profil`,route:`customer.profile`,icon:`M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z`}];if(t)return[{name:`Dashboard`,route:`reseller.dashboard`,icon:`M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21h6`},{name:`Belanja`,route:`catalog.index`,icon:`M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z`},{name:`Pesanan`,route:`reseller.orders`,icon:`M9 5H7a2 2 0 00-2-2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2`},{name:`Profil`,route:`reseller.profile`,icon:`M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z`}];if(n)return[{name:`Portal`,route:`artisan.dashboard`,icon:`M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z`},{name:`Katalog`,route:`catalog.index`,icon:`M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z`}];let r=[];return L.value.some(e=>e.route===`dashboard`)&&r.push({name:`Dashboard`,route:`dashboard`,icon:`M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6`}),L.value.some(e=>e.route===`sales.index`)&&r.push({name:`Penjualan`,route:`sales.index`,icon:`M9 8h6m-6 2h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z`}),L.value.some(e=>e.route===`finance.index`)&&r.push({name:`Keuangan`,route:`finance.index`,icon:`M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z`}),r});return(e,n)=>(t(),b(`div`,it,[p(v(Q)),y(`aside`,{class:u([`bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 flex-col shrink-0 hidden lg:flex h-screen sticky top-0 overflow-y-auto`,v(j).isSidebarOpen?`w-64`:`w-20`])},[y(`div`,at,[p(v(A),{href:e.route(T.value||`dashboard`),class:`flex items-center gap-2 overflow-hidden`},{default:c(()=>[v(C).logo_url?(t(),b(`img`,{key:0,src:`/storage/${v(C).logo_url}`,class:`w-8 h-8 object-contain rounded-lg flex-shrink-0 bg-white p-0.5 border border-gray-200`,alt:`Logo`},null,8,ot)):(t(),b(`div`,st,[...n[6]||=[y(`span`,{class:`text-white font-bold`},`R`,-1)]])),l(y(`div`,ct,[y(`span`,lt,f(v(C).business_name||`Rima Craft`),1)],512),[[k,v(j).isSidebarOpen]])]),_:1},8,[`href`])]),y(`nav`,ut,[(t(!0),b(_,null,a(I.value,n=>(t(),b(`div`,{key:n.title,class:`space-y-1`},[l(y(`span`,{class:`px-3 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1.5`},f(n.title),513),[[k,v(j).isSidebarOpen]]),l(y(`hr`,dt,null,512),[[k,!v(j).isSidebarOpen]]),(t(!0),b(_,null,a(n.items,n=>(t(),d(v(A),{key:n.name,href:e.route(n.route),class:u([`flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-all group`,F(n.route)?`bg-amber-500 text-gray-950 shadow-md shadow-amber-500/10`:`text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white`])},{default:c(()=>[(t(),b(`svg`,ft,[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:n.icon},null,8,pt)])),l(y(`span`,{class:`truncate`},f(n.name),513),[[k,v(j).isSidebarOpen]])]),_:2},1032,[`href`,`class`]))),128))]))),128))]),E.value?(t(),b(`div`,mt,[y(`button`,{onClick:D,class:u([`w-full flex items-center justify-center gap-2 py-2.5 bg-amber-500 hover:bg-amber-600 text-gray-950 rounded-lg text-xs font-bold transition shadow-sm`,v(j).isSidebarOpen?`px-3`:`px-0`]),title:`Download Aplikasi Rima Craft`},[n[7]||=y(`svg`,{class:`w-4 h-4`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4`})],-1),l(y(`span`,ht,`Download App`,512),[[k,v(j).isSidebarOpen]])],2)])):m(``,!0),l(y(`div`,gt,[n[8]||=y(`p`,{class:`text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2`},`Sponsor:`,-1),y(`div`,_t,[(t(!0),b(_,null,a(v(C).sponsors,(e,n)=>(t(),d(i(e.link?`a`:`div`),x({key:n,ref_for:!0},e.link?{href:e.link,target:`_blank`,rel:`noopener noreferrer`}:{},{class:`flex items-center gap-1.5 group`,title:e.description||e.name}),{default:c(()=>[e.logo_url?(t(),b(`img`,{key:0,src:e.logo_url.startsWith(`http`)||e.logo_url.startsWith(`/`)?e.logo_url:`/storage/${e.logo_url}`,class:`h-5 w-auto object-contain rounded bg-white p-0.5 border border-gray-200 group-hover:border-amber-300 transition-colors`,alt:e.name},null,8,vt)):(t(),b(`span`,yt,f(e.name),1))]),_:2},1040,[`title`]))),128))])],512),[[k,v(j).isSidebarOpen&&v(C).sponsors?.length]])],2),y(`div`,bt,[y(`header`,xt,[y(`div`,St,[y(`button`,{onClick:n[0]||=(...e)=>v(j).toggleSidebar&&v(j).toggleSidebar(...e),class:`p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors`},[...n[9]||=[y(`svg`,{class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M4 6h16M4 12h16M4 18h16`})],-1)]]),v(C).business_subtitle?(t(),b(`span`,Ct,f(v(C).business_subtitle),1)):m(``,!0)]),y(`div`,wt,[v(C).logo_url?(t(),b(`img`,{key:0,src:`/storage/${v(C).logo_url}`,class:`w-8 h-8 object-contain rounded-lg flex-shrink-0 bg-white p-0.5 border border-gray-250`,alt:`Logo`},null,8,Tt)):(t(),b(`div`,Et,[...n[10]||=[y(`span`,{class:`text-white font-bold`},`R`,-1)]])),y(`div`,Dt,[y(`span`,Ot,f(v(C).business_name||`Rima Craft`),1)])]),y(`div`,kt,[y(`button`,{onClick:n[1]||=(...e)=>v(O).toggle&&v(O).toggle(...e),class:`p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors`},[v(O).isDark?(t(),b(`svg`,At,[...n[11]||=[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z`},null,-1)]])):(t(),b(`svg`,jt,[...n[12]||=[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z`},null,-1)]]))]),y(`div`,Mt,[y(`div`,Nt,[y(`div`,Pt,f(v(S).name),1),y(`div`,Ft,f(v(S).email),1)]),y(`button`,{onClick:ne,class:`p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors`,title:`Keluar`},[...n[13]||=[y(`svg`,{class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1`})],-1)]])])])]),y(`main`,It,[y(`div`,null,[o(e.$slots,`default`)]),v(C).sponsors?.length?(t(),b(`div`,Lt,[n[14]||=y(`span`,{class:`text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest`},`Didukung & Disponsori Oleh:`,-1),y(`div`,Rt,[(t(!0),b(_,null,a(v(C).sponsors,(e,n)=>(t(),d(i(e.link?`a`:`div`),x({key:n},{ref_for:!0},e.link?{href:e.link,target:`_blank`,rel:`noopener noreferrer`}:{},{class:`flex items-center gap-2 group`,title:e.description||e.name}),{default:c(()=>[e.logo_url?(t(),b(`img`,{key:0,src:e.logo_url.startsWith(`http`)||e.logo_url.startsWith(`/`)?e.logo_url:`/storage/${e.logo_url}`,class:`h-5 w-auto object-contain rounded bg-white p-0.5 border border-gray-200 dark:border-gray-700 group-hover:border-amber-300 transition-colors`,alt:e.name},null,8,zt)):m(``,!0),y(`span`,Bt,f(e.name),1)]),_:2},1040,[`title`]))),128))])])):m(``,!0)])]),y(`div`,Vt,[y(`div`,Ht,[(t(!0),b(_,null,a(R.value,n=>(t(),d(v(A),{key:n.route,href:e.route(n.route),class:u([`flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center`,F(n.route)?`text-amber-500 font-bold`:`text-gray-500 dark:text-gray-400`])},{default:c(()=>[(t(),b(`svg`,Ut,[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:n.icon},null,8,Wt)])),y(`span`,Gt,f(n.name),1)]),_:2},1032,[`href`,`class`]))),128)),y(`button`,{type:`button`,onClick:n[2]||=e=>M.value=!0,class:`flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-gray-500 dark:text-gray-400 text-center`},[...n[15]||=[y(`svg`,{class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M4 6h16M4 12h16M4 18h16`})],-1),y(`span`,{class:`text-[10px]`},`Menu`,-1)]])])]),E.value?(t(),b(`button`,{key:0,type:`button`,onClick:D,class:`lg:hidden fixed bottom-20 right-4 z-[60] inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-3 text-sm font-bold text-gray-950 shadow-lg shadow-amber-500/20 transition hover:bg-amber-600`,title:`Download App`},[...n[16]||=[y(`svg`,{class:`h-5 w-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4`})],-1),ee(` Download App `,-1)]])):m(``,!0),l(y(`div`,Kt,[y(`div`,{onClick:n[3]||=e=>M.value=!1,class:`fixed inset-0 bg-black/60 backdrop-blur-sm`}),y(`div`,qt,[y(`div`,Jt,[n[18]||=y(`span`,{class:`font-bold text-gray-900 dark:text-white flex items-center gap-2`},[y(`div`,{class:`w-6 h-6 bg-amber-500 rounded flex items-center justify-center`},[y(`span`,{class:`text-white font-bold text-xs`},`R`)]),y(`span`,null,`Menu Navigasi`)],-1),y(`button`,{onClick:n[4]||=e=>M.value=!1,class:`p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800`},[...n[17]||=[y(`svg`,{class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M6 18L18 6M6 6l12 12`})],-1)]])]),y(`div`,Yt,[(t(!0),b(_,null,a(I.value,r=>(t(),b(`div`,{key:r.title,class:`space-y-1`},[y(`span`,Xt,f(r.title),1),(t(!0),b(_,null,a(r.items,r=>(t(),d(v(A),{key:r.name,href:e.route(r.route),onClick:n[5]||=e=>M.value=!1,class:u([`flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-all`,F(r.route)?`bg-amber-500 text-gray-950 shadow-md shadow-amber-500/10`:`text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white`])},{default:c(()=>[(t(),b(`svg`,Zt,[y(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:r.icon},null,8,Qt)])),y(`span`,$t,f(r.name),1)]),_:2},1032,[`href`,`class`]))),128))]))),128))])])],512),[[k,M.value]])]))}};export{Q as n,en as t};
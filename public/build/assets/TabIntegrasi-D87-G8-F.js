import{$ as e,A as t,D as n,M as r,N as i,U as a,ct as o,g as s,h as c,m as l,r as u,rt as d,s as f,u as p,w as m}from"./runtime-core.esm-bundler-4I4cuEBR.js";import{t as h}from"./classnames-CRHlWn3X.js";import{St as g,i as _}from"./app-DZyYDOU5.js";import{t as v}from"./axios-DAYYMcTd.js";import{t as y}from"./button-CiW-mBve.js";import{t as b}from"./baseeditableholder-B0lHIFli.js";import{t as x}from"./inputtext-vxbSBxGC.js";import{t as S}from"./select-wrzPw5vh.js";import{t as C}from"./dialog-D2HSoVvp.js";var w=_.extend({name:`toggleswitch`,style:`
    .p-toggleswitch {
        display: inline-block;
        width: dt('toggleswitch.width');
        height: dt('toggleswitch.height');
    }

    .p-toggleswitch-input {
        cursor: pointer;
        appearance: none;
        position: absolute;
        top: 0;
        inset-inline-start: 0;
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
        opacity: 0;
        z-index: 1;
        outline: 0 none;
        border-radius: dt('toggleswitch.border.radius');
    }

    .p-toggleswitch-slider {
        cursor: pointer;
        width: 100%;
        height: 100%;
        border-width: dt('toggleswitch.border.width');
        border-style: solid;
        border-color: dt('toggleswitch.border.color');
        background: dt('toggleswitch.background');
        transition:
            background dt('toggleswitch.transition.duration'),
            color dt('toggleswitch.transition.duration'),
            border-color dt('toggleswitch.transition.duration'),
            outline-color dt('toggleswitch.transition.duration'),
            box-shadow dt('toggleswitch.transition.duration');
        border-radius: dt('toggleswitch.border.radius');
        outline-color: transparent;
        box-shadow: dt('toggleswitch.shadow');
    }

    .p-toggleswitch-handle {
        position: absolute;
        top: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: dt('toggleswitch.handle.background');
        color: dt('toggleswitch.handle.color');
        width: dt('toggleswitch.handle.size');
        height: dt('toggleswitch.handle.size');
        inset-inline-start: dt('toggleswitch.gap');
        margin-block-start: calc(-1 * calc(dt('toggleswitch.handle.size') / 2));
        border-radius: dt('toggleswitch.handle.border.radius');
        transition:
            background dt('toggleswitch.transition.duration'),
            color dt('toggleswitch.transition.duration'),
            inset-inline-start dt('toggleswitch.slide.duration'),
            box-shadow dt('toggleswitch.slide.duration');
    }

    .p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-slider {
        background: dt('toggleswitch.checked.background');
        border-color: dt('toggleswitch.checked.border.color');
    }

    .p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.checked.background');
        color: dt('toggleswitch.handle.checked.color');
        inset-inline-start: calc(dt('toggleswitch.width') - calc(dt('toggleswitch.handle.size') + dt('toggleswitch.gap')));
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-slider {
        background: dt('toggleswitch.hover.background');
        border-color: dt('toggleswitch.hover.border.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.hover.background');
        color: dt('toggleswitch.handle.hover.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-slider {
        background: dt('toggleswitch.checked.hover.background');
        border-color: dt('toggleswitch.checked.hover.border.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.checked.hover.background');
        color: dt('toggleswitch.handle.checked.hover.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:focus-visible) .p-toggleswitch-slider {
        box-shadow: dt('toggleswitch.focus.ring.shadow');
        outline: dt('toggleswitch.focus.ring.width') dt('toggleswitch.focus.ring.style') dt('toggleswitch.focus.ring.color');
        outline-offset: dt('toggleswitch.focus.ring.offset');
    }

    .p-toggleswitch.p-invalid > .p-toggleswitch-slider {
        border-color: dt('toggleswitch.invalid.border.color');
    }

    .p-toggleswitch.p-disabled {
        opacity: 1;
    }

    .p-toggleswitch.p-disabled .p-toggleswitch-slider {
        background: dt('toggleswitch.disabled.background');
    }

    .p-toggleswitch.p-disabled .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.disabled.background');
    }
`,classes:{root:function(e){var t=e.instance,n=e.props;return[`p-toggleswitch p-component`,{"p-toggleswitch-checked":t.checked,"p-disabled":n.disabled,"p-invalid":t.$invalid}]},input:`p-toggleswitch-input`,slider:`p-toggleswitch-slider`,handle:`p-toggleswitch-handle`},inlineStyles:{root:{position:`relative`}}}),T={name:`ToggleSwitch`,extends:{name:`BaseToggleSwitch`,extends:b,props:{trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:w,provide:function(){return{$pcToggleSwitch:this,$parentInstance:this}}},inheritAttrs:!1,emits:[`change`,`focus`,`blur`],methods:{getPTOptions:function(e){return(e===`root`?this.ptmi:this.ptm)(e,{context:{checked:this.checked,disabled:this.disabled}})},onChange:function(e){if(!this.disabled&&!this.readonly){var t=this.checked?this.falseValue:this.trueValue;this.writeValue(t,e),this.$emit(`change`,e)}},onFocus:function(e){this.$emit(`focus`,e)},onBlur:function(e){var t,n;this.$emit(`blur`,e),(t=(n=this.formField).onBlur)==null||t.call(n,e)}},computed:{checked:function(){return this.d_value===this.trueValue},dataP:function(){return h({checked:this.checked,disabled:this.disabled,invalid:this.$invalid})}}},E=[`data-p-checked`,`data-p-disabled`,`data-p`],D=[`id`,`checked`,`tabindex`,`disabled`,`readonly`,`aria-checked`,`aria-labelledby`,`aria-label`,`aria-invalid`],O=[`data-p`],k=[`data-p`];function A(e,n,r,a,o,s){return t(),p(`div`,m({class:e.cx(`root`),style:e.sx(`root`)},s.getPTOptions(`root`),{"data-p-checked":s.checked,"data-p-disabled":e.disabled,"data-p":s.dataP}),[f(`input`,m({id:e.inputId,type:`checkbox`,role:`switch`,class:[e.cx(`input`),e.inputClass],style:e.inputStyle,checked:s.checked,tabindex:e.tabindex,disabled:e.disabled,readonly:e.readonly,"aria-checked":s.checked,"aria-labelledby":e.ariaLabelledby,"aria-label":e.ariaLabel,"aria-invalid":e.invalid||void 0,onFocus:n[0]||=function(){return s.onFocus&&s.onFocus.apply(s,arguments)},onBlur:n[1]||=function(){return s.onBlur&&s.onBlur.apply(s,arguments)},onChange:n[2]||=function(){return s.onChange&&s.onChange.apply(s,arguments)}},s.getPTOptions(`input`)),null,16,D),f(`div`,m({class:e.cx(`slider`)},s.getPTOptions(`slider`),{"data-p":s.dataP}),[f(`div`,m({class:e.cx(`handle`)},s.getPTOptions(`handle`),{"data-p":s.dataP}),[i(e.$slots,`handle`,{checked:s.checked})],16,k)],16,O)],16,E)}T.render=A;var j={class:`space-y-6 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-150 dark:border-gray-800`},M={class:`border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden bg-white dark:bg-gray-900`},N={class:`w-16 text-sm text-gray-500 dark:text-gray-400 pl-2 flex items-center`},P=[`onClick`],F={class:`text-sm font-medium text-gray-700 dark:text-gray-300`},I={class:`w-1/4 flex items-center gap-2`},L={key:0,class:`text-sm text-gray-600 dark:text-gray-400 font-mono tracking-widest`},R={key:1,class:`text-sm text-gray-800 dark:text-gray-200 font-mono`},z=[`onClick`],B={key:0,class:`w-4 h-4`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},V={key:1,class:`w-4 h-4`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},H={key:1,class:`text-xs text-gray-400 italic`},U={class:`w-1/6 flex justify-center`},W={class:`w-1/4 text-sm text-gray-500 dark:text-gray-400 pl-4`},G={class:`grid grid-cols-1 md:grid-cols-2 gap-6`},K={class:`flex flex-col gap-1.5`},q={class:`flex gap-2`},J={class:`flex flex-col gap-1.5`},Y={class:`flex flex-col gap-1.5`},X={class:`flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-800`},Z={class:`flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800`},Q={class:`flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800`},$={__name:`TabIntegrasi`,props:{form:Object,settings:Object},setup(i){let m=i,h=e([]),_=e([]),b=e({shipping:!1,payment:!1,qrisly:!1}),w=e=>{b.value[e]=!0},E=e({shipping:!1,payment:!1,qrisly:!1}),D=e=>{E.value[e]=!E.value[e]},O=e=>e?`•`.repeat(Math.min(e.length,16)):`-`,k=[{id:`shipping`,name:`Shipping Cost`,desc:`Pengaturan RajaOngkir untuk perhitungan ongkir otomatis.`,key:()=>m.form.rajaongkir_api_key,activeKey:`rajaongkir_enabled`,date:`29/07/2026`},{id:`payment`,name:`Payment API`,desc:`Pengaturan Gateway Pembayaran (Contoh: Midtrans).`,key:()=>``,activeKey:`payment_enabled`,date:`29/07/2026`},{id:`qrisly`,name:`QRISLY API`,desc:`Integrasi sistem pembayaran QRISLY.`,key:()=>``,activeKey:`qrisly_enabled`,date:`29/07/2026`}],A=async()=>{try{h.value=(await v.get(route(`api.shipping.provinces`),{params:{api_key:m.form.rajaongkir_api_key}})).data.map(e=>({label:e.province||e.name,value:e.province_id||e.id}))}catch{console.error(`Gagal memuat provinsi RajaOngkir`)}},$=async e=>{if(e)try{_.value=(await v.get(route(`api.shipping.cities`),{params:{province:e,api_key:m.form.rajaongkir_api_key}})).data.map(e=>({label:e.type&&e.city_name?`${e.type} ${e.city_name}`:e.name,value:e.city_id||e.id}))}catch{console.error(`Gagal memuat kota RajaOngkir`)}},ee=()=>{_.value=[],m.form.store_origin_city_id=null,$(m.form.store_origin_province_id)};n(async()=>{m.form.rajaongkir_api_key&&(await A(),m.form.store_origin_province_id&&await $(m.form.store_origin_province_id))});let te=async()=>{if(!m.form.rajaongkir_api_key){alert(`Silakan masukkan API Key RajaOngkir terlebih dahulu.`);return}await A(),h.value.length>0?alert(`Berhasil terhubung ke RajaOngkir! Silakan pilih Provinsi dan Kota Asal Pengiriman.`):alert(`Gagal terhubung! Pastikan API Key benar.`)};return(e,n)=>(t(),p(`div`,j,[n[20]||=f(`div`,{class:`mb-4`},[f(`h3`,{class:`text-lg font-bold text-gray-900 dark:text-white mb-1`},`Manage API Keys for every services that you use`),f(`p`,{class:`text-sm text-gray-500`},` Pilih layanan di bawah ini untuk mengatur API Key dan konfigurasi lainnya. `)],-1),f(`div`,M,[n[12]||=l(`<div class="flex items-center p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800"><div class="w-16 text-sm font-semibold text-gray-600 dark:text-gray-400"></div><div class="w-1/4 text-sm font-semibold text-gray-600 dark:text-gray-400">API Name</div><div class="w-1/4 text-sm font-semibold text-gray-600 dark:text-gray-400">API Key</div><div class="w-1/6 text-sm font-semibold text-gray-600 dark:text-gray-400 text-center">Status</div><div class="w-1/4 text-sm font-semibold text-gray-600 dark:text-gray-400">Added</div></div>`,1),(t(),p(u,null,r(k,(e,r)=>f(`div`,{key:e.id,class:`flex items-center p-4 border-b border-gray-100 dark:border-gray-800 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors`},[f(`div`,N,o(r+1),1),f(`div`,{class:`w-1/4 flex items-center gap-3 cursor-pointer group`,onClick:t=>w(e.id)},[f(`span`,F,o(e.name),1),n[9]||=f(`svg`,{class:`w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200 transition-colors`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[f(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M9 5l7 7-7 7`})],-1)],8,P),f(`div`,I,[e.key()?(t(),p(u,{key:0},[E.value[e.id]?(t(),p(`span`,R,o(e.key()),1)):(t(),p(`span`,L,o(O(e.key())),1)),f(`button`,{onClick:g(t=>D(e.id),[`stop`]),class:`flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-amber-600 transition ml-2`},[E.value[e.id]?(t(),p(`svg`,V,[...n[11]||=[f(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21`},null,-1)]])):(t(),p(`svg`,B,[...n[10]||=[f(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M15 12a3 3 0 11-6 0 3 3 0 016 0z`},null,-1),f(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z`},null,-1)]])),c(` `+o((E.value[e.id],``)),1)],8,z)],64)):(t(),p(`span`,H,`Not set`))]),f(`div`,U,[s(d(T),{modelValue:i.form[e.activeKey],"onUpdate:modelValue":t=>i.form[e.activeKey]=t},null,8,[`modelValue`,`onUpdate:modelValue`])]),f(`div`,W,o(e.date),1)])),64))]),s(d(C),{visible:b.value.shipping,"onUpdate:visible":n[4]||=e=>b.value.shipping=e,modal:``,header:`Pengaturan Shipping Cost (RajaOngkir)`,style:{width:`50rem`},breakpoints:{"1199px":`75vw`,"575px":`90vw`}},{default:a(()=>[n[17]||=f(`div`,{class:`text-sm text-gray-500 dark:text-gray-400 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4`},[c(` Hubungkan website Anda dengan API RajaOngkir untuk perhitungan ongkos kirim otomatis secara *real-time*. Anda bisa mendapatkan API Key gratis di `),f(`a`,{href:`https://rajaongkir.com`,target:`_blank`,class:`text-amber-600 font-bold hover:underline`},`rajaongkir.com`),c(`. `)],-1),f(`div`,G,[f(`div`,K,[n[13]||=f(`label`,{class:`text-sm font-semibold`},`RajaOngkir API Key`,-1),f(`div`,q,[s(d(x),{modelValue:i.form.rajaongkir_api_key,"onUpdate:modelValue":n[0]||=e=>i.form.rajaongkir_api_key=e,placeholder:`Ketik/Paste API Key di sini`,class:`w-full`},null,8,[`modelValue`]),f(`button`,{type:`button`,onClick:te,class:`px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-sm font-bold rounded-lg border border-gray-300 dark:border-gray-600 transition`},` Cek `)])]),f(`div`,J,[n[14]||=f(`label`,{class:`text-sm font-semibold`},`Provinsi Asal (Toko)`,-1),s(d(S),{modelValue:i.form.store_origin_province_id,"onUpdate:modelValue":n[1]||=e=>i.form.store_origin_province_id=e,options:h.value,optionLabel:`label`,optionValue:`value`,placeholder:`Pilih Provinsi`,class:`w-full`,onChange:ee},null,8,[`modelValue`,`options`])]),f(`div`,Y,[n[15]||=f(`label`,{class:`text-sm font-semibold`},`Kota Asal (Toko)`,-1),s(d(S),{modelValue:i.form.store_origin_city_id,"onUpdate:modelValue":n[2]||=e=>i.form.store_origin_city_id=e,options:_.value,optionLabel:`label`,optionValue:`value`,placeholder:`Pilih Kota Asal`,class:`w-full`},null,8,[`modelValue`,`options`]),n[16]||=f(`p`,{class:`text-xs text-gray-500 mt-1`},`Semua ongkos kirim akan dihitung dengan titik awal dari kota ini.`,-1)])]),f(`div`,X,[s(d(y),{label:`Selesai`,icon:`pi pi-check`,onClick:n[3]||=e=>b.value.shipping=!1,class:`!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold`})])]),_:1},8,[`visible`]),s(d(C),{visible:b.value.payment,"onUpdate:visible":n[6]||=e=>b.value.payment=e,modal:``,header:`Pengaturan Payment API`,style:{width:`40rem`},breakpoints:{"1199px":`75vw`,"575px":`90vw`}},{default:a(()=>[n[18]||=f(`div`,{class:`p-4 text-center`},[f(`i`,{class:`pi pi-cog text-4xl text-gray-300 mb-3`}),f(`p`,{class:`text-gray-500`},`Konfigurasi Payment API akan segera tersedia.`)],-1),f(`div`,Z,[s(d(y),{label:`Tutup`,severity:`secondary`,onClick:n[5]||=e=>b.value.payment=!1})])]),_:1},8,[`visible`]),s(d(C),{visible:b.value.qrisly,"onUpdate:visible":n[8]||=e=>b.value.qrisly=e,modal:``,header:`Pengaturan QRISLY API`,style:{width:`40rem`},breakpoints:{"1199px":`75vw`,"575px":`90vw`}},{default:a(()=>[n[19]||=f(`div`,{class:`p-4 text-center`},[f(`i`,{class:`pi pi-qrcode text-4xl text-gray-300 mb-3`}),f(`p`,{class:`text-gray-500`},`Konfigurasi QRISLY API akan segera tersedia.`)],-1),f(`div`,Q,[s(d(y),{label:`Tutup`,severity:`secondary`,onClick:n[7]||=e=>b.value.qrisly=!1})])]),_:1},8,[`visible`])]))}};export{$ as default};
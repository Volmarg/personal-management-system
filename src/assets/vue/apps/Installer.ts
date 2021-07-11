//@ts-ignore
import { createApp }      from 'vue/dist/vue.esm-bundler';
import InstallerComponent from "../components/installer/installer.vue";

let app = createApp({
    components: {
        "installer": InstallerComponent,
    },
});

app.mount("#installerApp")

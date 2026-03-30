<!--
  Toast notifications are handled globally via vue-toastification.
  They are configured and mounted in src/main.js:

    import Toast, { POSITION } from 'vue-toastification'
    import 'vue-toastification/dist/index.css'

    app.use(Toast, {
      position: POSITION.TOP_RIGHT,
      timeout: 3500,
      ...
    })

  To trigger a toast anywhere in the app:

    import { useToast } from 'vue-toastification'
    const toast = useToast()

    toast.success('Task created!')
    toast.error('Something went wrong.')
    toast.info('Tip: You can filter tasks by status.')
    toast.warning('This task is overdue.')

  The <ToastContainer /> component itself is not required in your template —
  vue-toastification inserts its own container into the DOM automatically.
  This file exists purely for documentation purposes.
-->
